<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SyaratUjianProdiController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('syarat-ujian', SyaratUjianProdiController::class)->only(['index', 'store', 'destroy']);
});

