<?php

use App\Http\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [MasterDataController::class, 'index'])->name('dashboard');
Route::get('/export-data', [MasterDataController::class, 'export'])->name('export');
Route::post('/upload', [MasterDataController::class, 'upload'])->name('upload');
Route::post('/upload-spatie', [MasterDataController::class, 'upload_simple_excel'])->name('upload_simple_excel');
