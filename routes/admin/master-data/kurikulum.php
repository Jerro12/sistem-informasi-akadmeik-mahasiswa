<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\KurikulumController;

Route::prefix('admin/master-data')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('kurikulum', KurikulumController::class)->except(['create', 'edit', 'show']);
});
