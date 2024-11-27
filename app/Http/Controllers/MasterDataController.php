<?php

namespace App\Http\Controllers;

use App\Models\MasterData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Storage;

use Carbon\Carbon;



class MasterDataController extends Controller
{
    public function index(Request $request)
    {
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $selectedKotarmh = $request->input('kota_rmh');
        $selectedKecrmh = $request->input('kec_rmh');
        $selectedKotaperush = $request->input('kota_perush');

        $query = MasterData::query();

        // Fetch distinct `kota_rmh` values for the dropdown
        $kotaOptions = MasterData::select('kota_rmh')
            ->distinct()
            ->whereNotNull('kota_rmh') // Exclude null values
            ->pluck('kota_rmh');
        $kecOptions = MasterData::select('kec_rmh')
            ->distinct()
            ->whereNotNull('kec_rmh') // Exclude null values
            ->pluck('kec_rmh');
        $kotaperushOptions = MasterData::select('kota_perush')
            ->distinct()
            ->whereNotNull('kota_perush') // Exclude null values
            ->pluck('kota_perush');

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
        if ($selectedKecrmh) {
            $query->where('kec_rmh', 'like', '%' . $selectedKecrmh . '%');
        }
        if ($selectedKotaperush) {
            $query->where('kota_perush', 'like', '%' . $selectedKotaperush . '%');
        }

        $master_data = $query->paginate(15)->withQueryString();
        return view(
            'index',
            compact(
                'master_data',
                'kotaOptions',
                'kecOptions',
                'kotaperushOptions',
                'selectedKotarmh',
                'selectedKecrmh',
                'selectedKotaperush'
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
        // ->each(function (array $rowProperties) {



        //     // Save each row to the database
        //     MasterData::create([
        //         
        //     ]);
        // });

        // Delete the file from storage after processing


        $this->saveDataToDatabase($rows);

        Storage::delete($tempPath);

        return response()->json(['message' => 'Success mang']);
    }


    public function export(Request $request)
    {
        $minAge = $request->input('min_age');
        $maxAge = $request->input('max_age');
        $selectedKotarmh = $request->input('kota_rmh');
        $selectedKecrmh = $request->input('kec_rmh');
        $selectedKotaperush = $request->input('kota_perush');

        $query = MasterData::query();

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
        if ($selectedKecrmh) {
            $query->where('kec_rmh', 'like', '%' . $selectedKecrmh . '%');
        }
        if ($selectedKotaperush) {
            $query->where('kota_perush', 'like', '%' . $selectedKotaperush . '%');
        }


        $zipFileName = 'export_data_' . now()->format('YmdHis') . '.zip';
        $zip = new \ZipArchive();
        $zipPath = storage_path("app/exports/{$zipFileName}");



        // Get the file by chunk
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) === true) {
            $fileIndex = 1;
            $query->chunk(1000000, function ($rows) use (&$zip, &$fileIndex) {
                $filename = "export_data_part_{$fileIndex}.csv";
                $filePath = storage_path("app/exports/{$filename}");
                $file = fopen($filePath, 'w');

                // Write headers
                fputcsv($file, ['Nama', 'DOB', 'Alamat Rumah', 'Kec_Rmh', 'Kota_Rmh', 'Perusahaan', 'Jabatan', 'Alamat_Perush', 'Kota_Perush', 'Kode_pos', 'Telp_Rumah', 'Telp_Kantor', 'Hp_2', 'Hp_Utama']);

                // Write rows
                foreach ($rows as $row) {
                    fputcsv($file, [
                        $row->nama,
                        $row->DOB,
                        $row->alamat_rumah,
                        $row->kec_rmh,
                        $row->kota_rmh,
                        $row->perusahaan,
                        $row->jabatan,
                        $row->alamat_perush,
                        $row->kota_perush,
                        $row->kode_pos,
                        $row->telp_rumah,
                        $row->telp_kantor,
                        $row->hp_2,
                        $row->hp_utama
                    ]);
                }

                fclose($file);
                $zip->addFile($filePath, $filename); // Add the CSV file to the ZIP
                $fileIndex++;
            });

            $zip->close();
        }
        return response()->download($zipPath)->deleteFileAfterSend(true);
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
    private function handleNA(String $value)
    {
        $invalidValues = ['N/A', 'None', 'Unknown', '', 'n/a', 'na'];  // Add more as needed
        return in_array($value, $invalidValues) ? null : $value;
    }
}
