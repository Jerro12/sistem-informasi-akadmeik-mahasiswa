<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;

echo "=== TAHUN AKADEMIK DI DATABASE ===\n";
foreach (TahunAkademik::all() as $ta) {
    echo "- ID: {$ta->id} | Nama: {$ta->nama} | Semester: {$ta->semester} | Aktif: " . ($ta->is_active ? 'YA' : 'TIDAK') . " | Biaya KRS: {$ta->biaya_krs}\n";
}

echo "\n=== DISTRIBUSI KELAS PER TAHUN AKADEMIK ===\n";
$kelasGroup = Kelas::select('tahun_akademik_id', \DB::raw('count(*) as total'))
    ->groupBy('tahun_akademik_id')
    ->get();
foreach ($kelasGroup as $kg) {
    $ta = TahunAkademik::find($kg->tahun_akademik_id);
    $taName = $ta ? "{$ta->nama} ({$ta->semester})" : 'UNKNOWN';
    echo "- Tahun Akademik ID: {$kg->tahun_akademik_id} ({$taName}) | Total Kelas: {$kg->total}\n";
}

echo "\n=== DAFTAR SEMUA MAHASISWA DAN SEMESTER ===\n";
foreach (Mahasiswa::with('prodi')->get() as $m) {
    echo "- NIM: {$m->nim} | Nama: {$m->nama} | Prodi: " . ($m->prodi?->nama ?? 'N/A') . " | Semester Sekarang: {$m->semester_sekarang}\n";
}
