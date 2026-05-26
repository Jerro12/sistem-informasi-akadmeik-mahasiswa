<?php

use App\Http\Controllers\Dosen\JadwalController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('jadwal', [JadwalController::class, 'index'])->name('jadwal.index');
});
