<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PenilaianController;

Route::middleware(['auth', 'role:admin', 'fakultas.scope'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/penilaian', [PenilaianController::class, 'index'])
            ->name('penilaian.index');
        Route::get('/penilaian/{kelas}', [PenilaianController::class, 'show'])
            ->name('penilaian.show');
        Route::post('/penilaian/{kelas}', [PenilaianController::class, 'store'])
            ->name('penilaian.store');
    });
