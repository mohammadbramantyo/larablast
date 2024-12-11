<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\SimpleExcel\SimpleExcelReader;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;


use App\Models\UploadHistory;


class UploadController extends Controller
{
    //

    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:csv,txt,xlsx|max:4096'
        ]);

        $file = $request->file('excel_file');


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
