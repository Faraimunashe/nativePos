<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login');

Route::middleware(['api.auth'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/pos', [PosController::class, 'index']);



});
