<?php

namespace App\Http\Controllers;

use App\Models\MasterData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
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

        return redirect()->back()->with('success', 'CSV imported successfully!');
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

    private function saveDataToDatabase($data)
    {
        foreach ($data as $row) {
            try {

                // Format data
                $formatted_dob = $this->formatDOB($row['DOB'] ?? null);
                $formatted_hp_utama = $this->formatPhone($row['Hp_Utama']);
                $formatted_hp_kedua = $this->formatPhone($row['Hp_2'] ?? null);


                // Save each row to the database
                MasterData::create([
                    'nama' => $row['Nama'],
                    'dob' => $formatted_dob,
                    'alamat_rumah' => $row['Alamat Rumah'],
                    'kec_rmh' => $row['Kec_Rmh'],
                    'kota_rmh' => $row['Kota_Rmh'],
                    'perusahaan' => $row['Perusahaan'],
                    'jabatan' => $row['Jabatan'],
                    'alamat_perush' => $row['Alamat_Perush'],
                    'kota_perush' => $row['Kota_Perush'],
                    'kode_pos' => $row['Kode_pos'],
                    'telp_rumah' => $row['Telp_Rumah'],
                    'telp_kantor' => $row['Telp_Kantor'],
                    'hp_2' => $formatted_hp_kedua,
                    'hp_utama' => $formatted_hp_utama
                ]);
            } catch (\Exception $e) {
                \Log::error('Error saving row: ', $row);
                \Log::error('Exception: ', ['message' => $e->getMessage()]);
            }
        }
    }


    private function formatDOB($dob)
    {
        if (!empty($dob)) {
            try {
                $dob = Carbon::createFromFormat('d/m/Y', $dob)->format('Y-m-d');
            } catch (\Exception $e) {
                $dob = null; // Set to null if format is invalid
                \Log::error('Error formatting dob:', $dob);
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
}
