<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Dosen\BiodataController;

Route::middleware(['auth', 'role:dosen'])->prefix('dosen')->name('dosen.')->group(function () {
    Route::get('/biodata', [BiodataController::class, 'index'])->name('biodata.index');
    Route::put('/biodata', [BiodataController::class, 'update'])->name('biodata.update');
    Route::put('/biodata/password', [BiodataController::class, 'updatePassword'])->name('biodata.update-password');
});
