<?php

use App\Http\Controllers\MasterDataController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });


Route::get('/', [MasterDataController::class, 'index']) -> name('dashboard');
Route::get('/export-data',[MasterDataController::class, 'export'])->name('export');
Route::post('/upload', [MasterDataController::class, 'upload']) -> name('upload');