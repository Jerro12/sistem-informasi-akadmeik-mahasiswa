<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = \App\Models\User::where('name', 'like', '%mahasiswa1%')->first();
if (!$user) {
    echo "User mahasiswa1 tidak ditemukan\n";
    exit;
}

$mhs = $user->mahasiswa;
echo "MHS Name: " . $user->name . "\n";
echo "MHS Prodi ID: " . $mhs->prodi_id . "\n";
echo "MHS Kurikulum ID: " . ($mhs->kurikulum_id ?? 'NULL') . "\n";

$tahunAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
echo "Tahun Aktif ID: " . ($tahunAktif ? $tahunAktif->id : 'NONE') . "\n";

$kelas = \App\Models\Kelas::where('tahun_akademik_id', $tahunAktif->id)->with('mataKuliah')->get();
echo "Total Kelas Tahun Aktif: " . $kelas->count() . "\n";

foreach ($kelas as $k) {
    echo "Kelas ID: " . $k->id . " | MK: " . $k->mataKuliah->nama_mk . " | Prodi MK: " . $k->mataKuliah->prodi_id . " | Kurikulum MK: " . ($k->mataKuliah->kurikulum_id ?? 'NULL') . "\n";
}
