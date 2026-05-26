<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mahasiswa\PaymentController;

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran.index');
    Route::post('/pembayaran', [PaymentController::class, 'store'])->name('pembayaran.store');
});
