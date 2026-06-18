<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahasiswa = App\Models\Mahasiswa::with(['user', 'prodi'])->first();
$ta = App\Models\TahunAkademik::where('is_active', true)->first();

$krs = App\Models\Krs::where('mahasiswa_id', $mahasiswa->id)
    ->where('tahun_akademik_id', $ta->id)
    ->first();

echo "=== CEK MATKUL SEMESTER 2 (takenMkIds check) ===\n";
$takenMkIds = App\Models\KrsDetail::whereHas('krs', function($q) use ($mahasiswa) {
    $q->where('mahasiswa_id', $mahasiswa->id);
})->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();

echo "takenMkIds (matkul yang PERNAH diambil di semua KRS): " . implode(', ', $takenMkIds) . "\n\n";

echo "=== KELAS SEMESTER 2 YANG TERSEDIA ===\n";
$kelasSem2 = App\Models\Kelas::with(['mataKuliah'])
    ->where('tahun_akademik_id', $ta->id)
    ->whereHas('mataKuliah', function($q) use ($mahasiswa) {
        $q->where('semester', '<', $mahasiswa->semester_sekarang)
          ->where('prodi_id', $mahasiswa->prodi_id);
    })
    ->get();
echo "Total kelas semester < 5: " . $kelasSem2->count() . "\n";

foreach ($kelasSem2 as $k) {
    $isTaken = in_array($k->mataKuliah->id, $takenMkIds);
    $alreadyInCurrentKrs = App\Models\KrsDetail::where('krs_id', $krs->id)->where('kelas_id', $k->id)->exists();
    echo "  " . ($isTaken ? '🚫 SUDAH PERNAH DIAMBIL' : ($alreadyInCurrentKrs ? '✅ ADA DI KRS INI' : '🔵 Belum diambil')) 
        . " [" . $k->mataKuliah->kode_mk . "] " . $k->mataKuliah->nama_mk 
        . " (Sem " . $k->mataKuliah->semester . ", MK ID: " . $k->mataKuliah->id . ")\n";
}

echo "\n=== CEK KELAS SEMESTER 5 YANG SEHARUSNYA MUNCUL ===\n";
$kelasSem5 = App\Models\Kelas::with(['mataKuliah', 'krsDetail'])
    ->where('tahun_akademik_id', $ta->id)
    ->whereHas('mataKuliah', function($q) use ($mahasiswa) {
        $q->where('semester', $mahasiswa->semester_sekarang)
          ->where('prodi_id', $mahasiswa->prodi_id);
    })
    ->get();
echo "Total kelas semester 5: " . $kelasSem5->count() . "\n";

foreach ($kelasSem5 as $k) {
    $alreadyInCurrentKrs = App\Models\KrsDetail::where('krs_id', $krs->id)->where('kelas_id', $k->id)->exists();
    echo "  " . ($alreadyInCurrentKrs ? '✅ SUDAH DI KRS INI (dikecualikan dari available)' : '🔵 Belum di KRS ini') 
        . " [" . $k->mataKuliah->kode_mk . "] " . $k->mataKuliah->nama_mk . "\n";
}

echo "\nSemua sem 5 sudah masuk KRS → wajar tidak tampil di available!\n";

echo "\n=== KESIMPULAN ===\n";
$details = $krs->krsDetail()->count();
$allSem5Count = $kelasSem5->count();
echo "Kelas semester 5 tersedia: $allSem5Count\n";
echo "Sudah diambil di KRS: $details\n";
if ($details >= $allSem5Count) {
    echo "✅ Mahasiswa sudah mengambil SEMUA mata kuliah semester 5!\n";
    echo "   Panel 'kelas tersedia' kosong karena tidak ada lagi yang bisa dipilih.\n";
    echo "   Mahasiswa bisa langsung klik 'Patenkan & Cetak KRS'.\n";
} else {
    echo "❌ Ada mata kuliah semester 5 yang belum diambil.\n";
}
