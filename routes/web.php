<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Config\EnvConfigController;
use App\Http\Controllers\EFTController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [LoginController::class, 'index'])->middleware('configs')->name('login');
Route::post('/login', [LoginController::class, 'login'])->middleware('configs')->name('login');


Route::get('/configs', [EnvConfigController::class, 'index'])->name('configs');
Route::post('/configs', [EnvConfigController::class, 'store'])->name('configs');

Route::middleware(['api.auth','configs'])->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/reports', [ReportController::class, 'index']);
    Route::get('/pos', [PosController::class, 'index']);

    Route::post('/cash', [PosController::class, 'store']);
    Route::post('/card', [EFTController::class, 'store']);


});
