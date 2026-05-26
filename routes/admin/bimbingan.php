<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\BimbinganController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope', 'prodi.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
    Route::get('/bimbingan/mahasiswa-monitor', [BimbinganController::class, 'mahasiswaMonitor'])->name('bimbingan.mahasiswa-monitor');
});
