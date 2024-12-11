<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\SimpleExcel\SimpleExcelWriter;

class ExportController extends Controller
{
    //

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
}
