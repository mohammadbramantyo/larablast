<?php

use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\UploadHistoryController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\UploadController;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);

    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);

    Route::get('forgot-password', [PasswordResetController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [MasterDataController::class, 'index'])->name('dashboard');
    Route::get('/export', [ExportController::class, 'export'])->name('export');
    Route::get('/upload-history', [UploadHistoryController::class, 'show'])->name('upload_history');


    Route::post('/upload', [UploadController::class, 'upload'])->name('upload');
    Route::post('/save-data-option', [UploadController::class, 'handleUserAction'])->name('save.data.option');
    Route::post('/clear-data', [MasterDataController::class, 'clear_database'])->name('clear_data');

    Route::delete('/delete/{id}',[MasterDataController::class, 'destroy'])->name('master_data.destroy');
});
Route::get('/test-routes', function () {
    return 'Test route is working!';
});
