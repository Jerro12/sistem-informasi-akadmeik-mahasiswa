<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\Krs;
use App\Models\KrsDetail;

$tahunAktif = TahunAkademik::where('is_active', true)->first();
$m = Mahasiswa::first();

if (!$m) {
    echo "Tidak ada mahasiswa di database!\n";
    exit;
}

echo "=== DIAGNOSIS DRAFT KRS UNTUK MAHASISWA {$m->nim} ===\n";
echo "Semester Sekarang: {$m->semester_sekarang}\n";

$krs = app(\App\Services\KrsService::class)->getActiveKrsOrNew($m);
echo "KRS ID: {$krs->id} | Status: {$krs->status}\n";

$details = KrsDetail::with(['kelas.mataKuliah', 'kelas.dosen.user'])
    ->where('krs_id', $krs->id)
    ->get();

echo "\n--- KELAS YANG SUDAH ADA DI DRAFT KRS MAHASISWA ({$details->count()} kelas) ---\n";
foreach ($details as $d) {
    $kelas = $d->kelas;
    $mk = $kelas->mataKuliah;
    echo "- Detail ID: {$d->id} | Kelas ID: {$kelas->id} | Nama: {$kelas->nama} | Matkul: {$mk->nama} (Smt {$mk->semester})\n";
}

echo "\n--- SEMUA KELAS TERSEDIA DI DB UNTUK TAHUN AKADEMIK AKTIF (TA ID {$tahunAktif->id}) ---\n";
$allKelas = Kelas::with('mataKuliah')
    ->where('tahun_akademik_id', $tahunAktif->id)
    ->get();
foreach ($allKelas as $k) {
    $mk = $k->mataKuliah;
    echo "- Kelas ID: {$k->id} | Nama: {$k->nama} | Matkul: {$mk->nama} (Smt {$mk->semester}) | Prodi ID: {$mk->prodi_id} | Kurikulum ID: {$mk->kurikulum_id} | Konsentrasi ID: " . ($mk->konsentrasi_id ?: 'NULL') . "\n";
}
