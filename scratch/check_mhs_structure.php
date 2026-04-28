<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mhs = \App\Models\Mahasiswa::first();
echo "Nama: " . ($mhs->user->name ?? 'N/A') . "\n";
echo "Angkatan: " . ($mhs->angkatan ?? 'N/A') . "\n";
echo "Semester: " . ($mhs->semester ?? 'N/A') . "\n";

$columns = \Illuminate\Support\Facades\Schema::getColumnNames('mahasiswas');
if (empty($columns)) $columns = \Illuminate\Support\Facades\Schema::getColumnNames('mahasiswa');
echo "Columns in mahasiswa table: " . implode(', ', $columns) . "\n";
