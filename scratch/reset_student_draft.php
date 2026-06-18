<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mahasiswa;
use App\Models\Krs;
use App\Models\KrsDetail;

$m = Mahasiswa::where('nim', '2022101001')->first();
if (!$m) {
    echo "Mahasiswa tidak ditemukan!\n";
    exit;
}

$krs = app(\App\Services\KrsService::class)->getActiveKrsOrNew($m);
echo "KRS ID: {$krs->id} | Status: {$krs->status}\n";

// Hapus krs_details agar draft kosong
$deletedCount = KrsDetail::where('krs_id', $krs->id)->delete();
echo "Berhasil menghapus {$deletedCount} kelas dari draft KRS Budi Santoso.\n";

// Sekarang jalankan check_all_students_krs.php
require __DIR__ . '/check_all_students_krs.php';
