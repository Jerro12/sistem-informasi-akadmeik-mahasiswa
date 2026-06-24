<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\PejabatController;
use App\Http\Controllers\Admin\KelasController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Manajemen Pejabat (Superadmin)
    Route::get('/pejabat', [PejabatController::class, 'index'])->name('pejabat.index');
    Route::put('/pejabat/prodi/{prodi}', [PejabatController::class, 'updateProdi'])->name('pejabat.update-prodi');
    Route::put('/pejabat/fakultas/{fakultas}', [PejabatController::class, 'updateFakultas'])->name('pejabat.update-fakultas');

});
