<?php

namespace App\Http\Controllers;

use App\Models\MasterData;
use App\Models\UploadHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\SimpleExcel\SimpleExcelWriter;

class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $selectedKotarmh = $request->input('kota_rmh');
        $selectedJabatan = $request->input('jabatan');
        $selectedKotaperush = $request->input('kota_perush');

        $query = MasterData::query();

        // Fetch distinct `kota_rmh` values for the dropdown
        $kota_rmh_options = Cache::rememberForever('city_home_options', function () {
            return DB::table('master_data')
                ->select('kota_rmh')
                ->distinct()
                ->whereNotNull('kota_rmh')
                ->pluck('kota_rmh');
        });

        $kota_perush_options = Cache::rememberForever('city_work_options', function () {
            return DB::table('master_data')
                ->select('kota_perush')
                ->distinct()
                ->whereNotNull('kota_perush')
                ->pluck('kota_perush');
        });

        $jabatan_options = Cache::rememberForever('jabatan_options', function () {
            return DB::table('master_data')
                ->select('jabatan')
                ->distinct()
                ->whereNotNull('jabatan')
                ->pluck('jabatan');
        });



        // Apply age filter
        if ($minAge) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?', [$minAge]);
        }
        if ($maxAge) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?', [$maxAge]);
        }

        // Apply filter domisili
        if ($selectedKotarmh) {
            $query->where('kota_rmh', 'like', '%' . $selectedKotarmh . '%');
        }
        if ($selectedKotaperush) {
            $query->where('kota_perush', 'like', '%' . $selectedKotaperush . '%');
        }
        if ($selectedJabatan) {
            $query->where('jabatan', 'like', '%' . $selectedJabatan . '%');
        }


        // Get Chart Data
        $topJobs = DB::select('
            SELECT COALESCE(jabatan, "Unknown") AS job, 
            COUNT(*) AS count
            FROM master_data
            GROUP BY jabatan
            ORDER BY count DESC
            LIMIT 5
        ');

        $topCity = DB::select('
            SELECT COALESCE(kota_rmh, "Unknown") AS city, 
            COUNT(*) AS count
            FROM master_data
            GROUP BY kota_rmh
            ORDER BY count DESC
            LIMIT 5
        ');


        // Others category count
        $othersCountJob = DB::table('master_data')
            ->whereNotIn('jabatan', array_column($topJobs, 'job'))
            ->count();
        $othersCountCity = DB::table('master_data')
            ->whereNotIn('kota_rmh', array_column($topJobs, 'city'))
            ->count();

        if ($othersCountJob > 0) {
            $topJobs[] = (object) [
                'job' => 'Others',
                'count' => $othersCountJob,
            ];
        }
        if ($othersCountCity > 0) {
            $topCity[] = (object) [
                'city' => 'Others',
                'count' => $othersCountCity,
            ];
        }


        $job_labels = array_column($topJobs, 'job');
        $job_counts = array_column($topJobs, 'count');
        $city_labels = array_column($topCity, 'city');
        $city_counts = array_column($topCity, 'count');



        $master_data = $query->paginate(15)->withQueryString();
        return view(
            'index',
            compact(
                'master_data',
                'job_labels',
                'job_counts',
                'city_labels',
                'city_counts',
                'kota_rmh_options',
                'kota_perush_options',
                'jabatan_options',
                'selectedKotarmh',
                'selectedJabatan',
                'selectedKotaperush',
            )
        );
    }

    public function upload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:4096',
        ]);

        // Get the uploaded file
        $file = $request->file('csv_file');

        // Get file contents
        $fileContent = file_get_contents($file);

        // Convert the file content encoding to UTF-8 without BOM
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', 'UTF-8');

        // Remove BOM if present
        $fileContent = preg_replace('/^\xEF\xBB\xBF/', '', $fileContent);

        // Save the clean content back to a temporary file
        $cleanFilePath = storage_path('app/cleaned_csv.csv');
        file_put_contents($cleanFilePath, $fileContent);

        // Now open the cleaned file for processing
        $handle = fopen($cleanFilePath, 'r');

        // Read the headers with the correct delimiter
        $headers = fgetcsv($handle, 0, ';');
        $headers = array_map('trim', $headers); // Trim data 

        $data = [];

        // Loop through rows
        while (($row = fgetcsv($handle, 0, ';')) !== false) {
            if (count($row) === count($headers)) {

                // Map the headers and row
                $rowData = array_combine($headers, $row);

                // Clean the row data (replace 'n/a', 'N/A', etc. with null)
                $rowData = array_map(function ($value) {
                    // Convert 'n/a', 'N/A' or any variant to null
                    return in_array(strtolower($value), ['n/a', 'na', 'null', '']) ? null : $value;
                }, $rowData);


                $data[] = $rowData;
            } else {
                \Log::warning('Malformed row detected: ', $row); // Log malformed row
            }
        }
        fclose($handle);

        // Process the data (for example, save to database)
        $this->saveDataToDatabase($data);

        return response()->json(['message' => 'Success mang']);
    }

    public function upload_simple_excel(Request $request)
    {
        $request->validate([
            'spatie-excel' => 'required|mimes:csv,txt,xlsx|max:4096'
        ]);

        $file = $request->file('spatie-excel');


        // Get file contents
        $fileContent = file_get_contents($file);

        // Save the clean content back to a temporary file
        $tempPath = storage_path('app/temp/uploaded_file.' . $file->getClientOriginalExtension());
        file_put_contents($tempPath, $fileContent);

        $rows = SimpleExcelReader::create($tempPath)
            ->useDelimiter(';')
            ->noHeaderRow()
            ->getRows()
            ->skip(1);

        $dataProperties = $this->saveDataTemporary($rows);

        Storage::delete($tempPath);

        return view('dataConfirmation', $dataProperties);
    }

    public function handleUserAction(Request $request)
    {
        $action = $request->input('action');

        $totalRows = $request->input('totalRows');
        $duplicates = $request->input('duplicates');
        $validRows = $request->input('validRows');

        Cache::forget('city_home_options');
        Cache::forget('city_work_options');
        Cache::forget('jabatan_options');

        if ($action == 'save_valid') {
            DB::statement('INSERT INTO master_data (nama, dob, alamat_rumah, kec_rmh, kota_rmh, perusahaan, jabatan, alamat_perush, kota_perush, kode_pos, telp_kantor, hp_2, hp_utama)
                            SELECT nama, dob, alamat_rumah, kec_rmh, kota_rmh, perusahaan, jabatan, alamat_perush, kota_perush, kode_pos, telp_kantor, hp_2, hp_utama
                            FROM temp_master_data
                            WHERE is_duplicate = 0
                            ');

            UploadHistory::create([
                'saved_rows' => $validRows,
                'processed_rows' => $totalRows,
                'duplicate_rows' => $duplicates,
                'valid_rows' => $validRows
            ]);
        } else if ($action == 'save_all') {
            DB::statement('INSERT INTO master_data (nama, dob, alamat_rumah, kec_rmh, kota_rmh, perusahaan, jabatan, alamat_perush, kota_perush, kode_pos, telp_kantor, hp_2, hp_utama)
                            SELECT nama, dob, alamat_rumah, kec_rmh, kota_rmh, perusahaan, jabatan, alamat_perush, kota_perush, kode_pos, telp_kantor, hp_2, hp_utama
                            FROM temp_master_data
                            ');

            UploadHistory::create([
                'saved_rows' => $totalRows,
                'processed_rows' => $totalRows,
                'duplicate_rows' => $duplicates,
                'valid_rows' => $validRows
            ]);
        }

        if ($action == 'cancel') {
            DB::statement('DROP TABLE IF EXISTS temp_master_data');

            return redirect()->route('dashboard')->with('info', 'Action Cancelled no data saved');
        }

        // Delete temporary table after
        DB::statement('DROP TABLE IF EXISTS temp_master_data');

        return redirect()->route('dashboard')->with('success', 'Data has been processed');
    }


    public function export(Request $request)
    {
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $selectedKotarmh = $request->input('kota_rmh');
        $selectedJabatan = $request->input('jabatan');
        $selectedKotaperush = $request->input('kota_perush');

        $query = DB::table('master_data')->orderBy('created_at');

        // Apply age filter
        if ($minAge) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) >= ?', [$minAge]);
        }
        if ($maxAge) {
            $query->whereRaw('TIMESTAMPDIFF(YEAR, dob, CURDATE()) <= ?', [$maxAge]);
        }

        // Apply filter domisili
        if ($selectedKotarmh) {
            $query->where('kota_rmh', 'like', '%' . $selectedKotarmh . '%');
        }
        if ($selectedJabatan) {
            $query->where('jabatan', 'like', '%' . $selectedJabatan . '%');
        }
        if ($selectedKotaperush) {
            $query->where('kota_perush', 'like', '%' . $selectedKotaperush . '%');
        }



        $zipFileName = 'export_data_' . now()->format('YmdHis') . '.zip';
        $zip = new \ZipArchive();
        $zipPath = storage_path("app/exports/{$zipFileName}");

        $max_rows_per_files = 100000;




        // split into files and put it in zip
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $fileIndex = 1;

            $query->chunk($max_rows_per_files, function ($rows) use (&$zip, &$fileIndex) {
                $file_name = "export_data_part_{$fileIndex}.xlsx";
                $file_path = storage_path("app/exports/export_data_part_{$file_name}.xlsx");

                $writer = SimpleExcelWriter::create($file_path, 'xlsx');
                $writer->addHeader([
                    'Nama',
                    'DOB',
                    'Alamat Rumah',
                    'Kec_Rmh',
                    'Kota_Rmh',
                    'Perusahaan',
                    'Jabatan',
                    'Alamat_Perush',
                    'Kota_Perush',
                    'Kode_pos',
                    'Telp_Rumah',
                    'Telp_Kantor',
                    'Hp_2',
                    'Hp_Utama'
                ]);

                foreach ($rows as $row) {
                    $writer->addRow([
                        'Nama' => $row->nama,
                        'DOB' => $row->dob,
                        'Alamat Rumah' => $row->alamat_rumah,
                        'Kec_Rmh' => $row->kec_rmh,
                        'Kota_Rmh' => $row->kota_rmh,
                        'Perusahaan' => $row->perusahaan,
                        'Jabatan' => $row->jabatan,
                        'Alamat_Perush' => $row->alamat_perush,
                        'Kota_Perush' => $row->kota_perush,
                        'Kode_pos' => $row->kode_pos,
                        'Telp_Rumah' => $row->telp_rumah,
                        'Telp_Kantor' => $row->telp_kantor,
                        'Hp_2' => $row->hp_2,
                        'Hp_Utama' => $row->hp_utama
                    ]);
                }

                $writer->close();
                $zip->addFile($file_path, $file_name); // Add the excel file to the ZIP
                $fileIndex++;
            });

            $zip->close();
        }
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function saveDataTemporary($rows)
    {

        // Clears table
        DB::statement('DROP TABLE IF EXISTS temp_master_data');

        // Create temporary table and add duplicate flag
        DB::statement('CREATE TABLE temp_master_data LIKE master_data');
        DB::statement('ALTER TABLE temp_master_data ADD COLUMN is_duplicate INT DEFAULT 0');


        // HashMap to check phone duplicate
        $seen_phone = DB::table('master_data')
            ->pluck('hp_utama')
            ->mapWithKeys(function ($phone) {
                return [$phone => 0];
            })
            ->toArray();

        $batch_data = [];
        $duplicate_count = 0;

        // Get batch data
        foreach ($rows as $row) {

            // Get data and handle empty or null or n/a data
            $nama = $this->handleNA($row[0]);
            $dob = $this->handleNA($row[1]);
            $alamat_rumah = $this->handleNA($row[2]);
            $kec_rmh = $this->handleNA($row[3]);
            $kota_rmh = $this->handleNA($row[4]);
            $perusahaan = $this->handleNA($row[5]);
            $jabatan = $this->handleNA($row[6]);
            $alamat_perush = $this->handleNA($row[7]);
            $kota_perush = $this->handleNA($row[8]);
            $kode_pos = $this->handleNA($row[9]);
            $telp_rumah = $this->handleNA($row[10]);
            $telp_kantor = $this->handleNA($row[11]);
            $hp_2 = $this->handleNA($row[12]);
            $hp_utama = $this->handleNA($row[13]);

            // Format data
            $formatted_dob = $this->formatDOB($dob);
            $formatted_hp_utama = $this->formatPhone($hp_utama);
            $formatted_hp_kedua = $this->formatPhone($hp_2);

            // Check duplicates for phone number
            $is_duplicate = 0;

            if (isset($seen_phone[$formatted_hp_utama])) {
                $seen_phone[$formatted_hp_utama]++;
                $duplicate_count++;
                $is_duplicate = 1;
            } else {
                $seen_phone[$formatted_hp_utama] = 1;
            }


            $batch_data[] = [
                'nama' => $nama,
                'dob' => $formatted_dob,
                'alamat_rumah' => $alamat_rumah,
                'kec_rmh' => $kec_rmh,
                'kota_rmh' => $kota_rmh,
                'perusahaan' => $perusahaan,
                'jabatan' => $jabatan,
                'alamat_perush' => $alamat_perush,
                'kota_perush' => $kota_perush,
                'kode_pos' => $kode_pos,
                'telp_rumah' => $telp_rumah,
                'telp_kantor' => $telp_kantor,
                'hp_2' => $formatted_hp_kedua,
                'hp_utama' => $formatted_hp_utama,
                'is_duplicate' => $is_duplicate
            ];
        }
        try {
            //Optimized insert number for sql see 
            // https://www.red-gate.com/simple-talk/databases/sql-server/performance-sql-server/comparing-multiple-rows-insert-vs-single-row-insert-with-three-data-load-methods/
            $chunkData = array_chunk($batch_data, 25);

            // Bulk insert data by chunks
            foreach ($chunkData as $chunk) {


                DB::table('temp_master_data')->insert($chunk);
            }
        } catch (\Throwable $th) {
            throw $th;
        }

        return [
            'totalRows' => count($batch_data),
            'duplicates' => $duplicate_count,
            'validRows' => count($batch_data) - $duplicate_count
        ];
    }

    public function saveDataToDatabase($rows)
    {
        $batch = [];

        // Get batch data
        foreach ($rows as $row) {

            // Get data and handle empty or null or n/a data
            $nama = $this->handleNA($row[0]);
            $dob = $this->handleNA($row[1]);
            $alamat_rumah = $this->handleNA($row[2]);
            $kec_rmh = $this->handleNA($row[3]);
            $kota_rmh = $this->handleNA($row[4]);
            $perusahaan = $this->handleNA($row[5]);
            $jabatan = $this->handleNA($row[6]);
            $alamat_perush = $this->handleNA($row[7]);
            $kota_perush = $this->handleNA($row[8]);
            $kode_pos = $this->handleNA($row[9]);
            $telp_rumah = $this->handleNA($row[10]);
            $telp_kantor = $this->handleNA($row[11]);
            $hp_2 = $this->handleNA($row[12]);
            $hp_utama = $this->handleNA($row[13]);

            // Format data
            $formatted_dob = $this->formatDOB($dob);
            $formatted_hp_utama = $this->formatPhone($hp_utama);
            $formatted_hp_kedua = $this->formatPhone($hp_2);

            $batch[] = [
                'nama' => $nama,
                'dob' => $formatted_dob,
                'alamat_rumah' => $alamat_rumah,
                'kec_rmh' => $kec_rmh,
                'kota_rmh' => $kota_rmh,
                'perusahaan' => $perusahaan,
                'jabatan' => $jabatan,
                'alamat_perush' => $alamat_perush,
                'kota_perush' => $kota_perush,
                'kode_pos' => $kode_pos,
                'telp_rumah' => $telp_rumah,
                'telp_kantor' => $telp_kantor,
                'hp_2' => $formatted_hp_kedua,
                'hp_utama' => $formatted_hp_utama
            ];
        }
        try {
            $chunkData = array_chunk($batch, 25);

            // Bulk insert data by chunks
            foreach ($chunkData as $chunk) {


                DB::table('master_data')->insert($chunk);
            }
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    private function formatDOB($dob)
    {

        // If $dob is already a DateTime or Carbon instance, format it
        if ($dob instanceof \DateTimeImmutable || $dob instanceof \Carbon\Carbon) {
            return $dob->format('Y-m-d');  // Return the formatted date string
        }

        if (!empty($dob)) {
            try {
                // check if dob exist
                if (strtolower($dob))

                    // Check if the dob is already in Y-m-d format
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
                        // Return as is
                        return $dob;
                    }


                $dob = Carbon::createFromFormat('d/m/Y', $dob)->format('Y-m-d');
            } catch (\Exception $e) {
                // $dob = null; // Set to null if format is invalid
                \Log::error('Error formatting dob:', [$dob]);
            }
        }
        return $dob;
    }

    private function formatPhone($phone)
    {
        if (!empty($phone)) {
            try {
                $phone = preg_replace('/^\+62/', '62', $phone);
                $phone = preg_replace('/^08/', '628', $phone);
            } catch (\Throwable $th) {
                //throw $th;
                $phone = null;
                \Log::error('Error formatting phone number:', $phone);
            }
        }

        return $phone;
    }

    /**
     * Handle N/A in file
     * @param $value string
     */
    private function handleNA($value)
    {
        $invalidValues = ['N/A', 'None', 'Unknown', '', 'n/a', 'na'];  // Add more as needed

        // If the value is a DateTimeImmutable, return it as-is
        if ($value instanceof \DateTimeImmutable) {
            return $value;
        }

        return in_array($value, $invalidValues) ? null : $value;
    }
}
