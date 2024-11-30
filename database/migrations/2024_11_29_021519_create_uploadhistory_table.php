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
        Schema::create('upload_history', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->bigInteger('saved_rows');
            $table->bigInteger('processed_rows');
            $table->bigInteger('duplicate_rows');
            $table->bigInteger('valid_rows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('upload_history');
    }
};
