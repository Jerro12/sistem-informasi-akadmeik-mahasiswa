<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SkripsiController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope', 'prodi.scope'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/skripsi', [SkripsiController::class, 'index'])->name('skripsi.index');
    Route::get('/skripsi/{skripsi}', [SkripsiController::class, 'show'])->name('skripsi.show');
    Route::post('/skripsi/{skripsi}/assign-pembimbing', [SkripsiController::class, 'assignPembimbing'])->name('skripsi.assign-pembimbing');
    Route::put('/skripsi/{skripsi}/status', [SkripsiController::class, 'updateStatus'])->name('skripsi.update-status');
    Route::put('/skripsi/{skripsi}/nilai', [SkripsiController::class, 'updateNilai'])->name('skripsi.update-nilai');

    // Exam Pendaftaran & Jadwal Routes
    Route::get('/pendaftaran-ujian', [SkripsiController::class, 'pendaftaranUjianList'])->name('pendaftaran-ujian.index');
    Route::get('/pendaftaran-ujian/{ujian}', [SkripsiController::class, 'pendaftaranUjianShow'])->name('pendaftaran-ujian.show');
    Route::post('/pendaftaran-ujian/{ujian}/verify-syarat/{syarat}', [SkripsiController::class, 'verifySyarat'])->name('pendaftaran-ujian.verify-syarat');
    Route::post('/pendaftaran-ujian/{ujian}/set-jadwal', [SkripsiController::class, 'setJadwalUjian'])->name('pendaftaran-ujian.set-jadwal');
    Route::post('/pendaftaran-ujian/{ujian}/approve', [SkripsiController::class, 'approvePendaftaran'])->name('pendaftaran-ujian.approve');
    Route::post('/pendaftaran-ujian/{ujian}/reject', [SkripsiController::class, 'rejectPendaftaran'])->name('pendaftaran-ujian.reject');
});
