<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\KonsentrasiController;

Route::prefix('admin/master-data')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('konsentrasi', KonsentrasiController::class)->except(['create', 'edit', 'show']);
});
