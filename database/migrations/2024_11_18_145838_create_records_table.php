<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('master_data', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('nama'); // Name
            $table->date('dob') ->nullable(); // Date of Birth
            $table->string('alamat_rumah') -> nullable(); // Home Address
            $table->string('kec_rmh') ->nullable(); // District (Kecamatan) for Home
            $table->string('kota_rmh') -> nullable(); // City (Kota) for Home
            $table->string('perusahaan')->nullable(); // Company (optional)
            $table->string('jabatan')->nullable(); // Job Title
            $table->string('alamat_perush')->nullable(); // Company Address
            $table->string('kota_perush')->nullable(); // Company City
            $table->string('kode_pos')->nullable(); // Postal Code
            $table->string('telp_rumah')->nullable(); // Home Phone
            $table->string('telp_kantor')->nullable(); // Office Phone
            $table->string('hp_2')->nullable(); // Secondary Mobile Phone
            $table->string('hp_utama'); // Primary Mobile Phone
            $table->timestamps(); // Laravel timestamps

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_data');
    }
};
