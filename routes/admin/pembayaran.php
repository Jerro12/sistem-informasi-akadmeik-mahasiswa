<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PembayaranController;

Route::middleware(['auth', 'role:superadmin'])->prefix('admin')->name('admin.')->group(function () {
    // Monitoring Pembayaran (Hanya Superadmin)
    Route::get('/pembayaran', [PembayaranController::class, 'index'])->name('pembayaran.index');
    Route::get('/pembayaran/{pembayaran}', [PembayaranController::class, 'show'])->name('pembayaran.show');

    // Pengaturan Biaya Kuliah (Hanya Superadmin)
    Route::get('/biaya-kuliah', [\App\Http\Controllers\Admin\BiayaKuliahController::class, 'index'])->name('biaya-kuliah.index');
    Route::post('/biaya-kuliah', [\App\Http\Controllers\Admin\BiayaKuliahController::class, 'store'])->name('biaya-kuliah.store');
    Route::delete('/biaya-kuliah/{biayaKuliah}', [\App\Http\Controllers\Admin\BiayaKuliahController::class, 'destroy'])->name('biaya-kuliah.destroy');
});
