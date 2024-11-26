<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Faker\Factory as Faker;

class GenerateMasterDataCSV extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:masterdata-csv {filename=dummy-data-10000.csv}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a dummy CSV file for the master_data table';
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filename = $this->argument('filename');
        $filePath = storage_path("app/{$filename}");

        // Open the file for writing
        $file = fopen($filePath, 'w');

        // Define the headers
        $headers = [
            'Nama',
            'DOB',
            'Alamat_Rumah',
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
        ];

        // Write the headers to the CSV file
        fputcsv($file, $headers, separator:";");


        // Generate 100,000 rows using Faker
        $faker = Faker::create();
        for ($i = 1; $i <= 10000; $i++) {
            $row = [
                'Nama'          => $faker->name,
                'DOB'           => $faker->date('Y-m-d'),
                'Alamat_Rumah'  => $faker->address,
                'Kec_Rmh'       => $faker->citySuffix, // Generates district-like names
                'Kota_Rmh'      => $faker->city,
                'Perusahaan'    => $faker->company,
                'Jabatan'       => $faker->jobTitle,
                'Alamat_Perush' => $faker->address,
                'Kota_Perush'   => $faker->city,
                'Kode_pos'      => $faker->postcode,
                'Telp_Rumah'    => $faker->phoneNumber,
                'Telp_Kantor'   => $faker->phoneNumber,
                'Hp_2'          => $faker->phoneNumber,
                'Hp_Utama'      => $faker->phoneNumber,
            ];

            // Write the row to the file
            fputcsv($file, $row, separator:";");

            // Optional: Show progress for large datasets
            if ($i % 10000 === 0) {
                echo "Generated $i rows...\n";
            }
        }

        // Close the file
        fclose($file);

        $this->info("Dummy CSV file created at: {$filePath}");
    }
}
