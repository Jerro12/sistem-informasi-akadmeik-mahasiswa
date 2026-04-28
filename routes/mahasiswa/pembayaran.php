<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Mahasiswa\PaymentController;

Route::middleware(['auth', 'role:mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(function () {
    Route::get('/pembayaran', [PaymentController::class, 'index'])->name('pembayaran.index');
});

// Webhook outside group or with different middleware if needed, but since it's from Midtrans it won't have Auth session
Route::post('/payment/webhook', [PaymentController::class, 'webhook'])->name('payment.webhook');
