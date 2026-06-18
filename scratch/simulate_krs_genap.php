<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;

// Ambil tahun akademik genap (misal ID 2)
$tahunGenap = TahunAkademik::where('semester', 'Genap')->first();
if (!$tahunGenap) {
    echo "Tahun Akademik Genap tidak ditemukan!\n";
    exit;
}

$m = Mahasiswa::where('nim', '2022101001')->first();
if (!$m) {
    echo "Mahasiswa tidak ditemukan!\n";
    exit;
}

echo "=== SIMULASI KRS SEMESTER GENAP ===\n";
echo "Mahasiswa NIM: {$m->nim} | Semester Sekarang di DB: {$m->semester_sekarang}\n";
echo "Tahun Akademik Simulasi: ID {$tahunGenap->id} | Semester: {$tahunGenap->semester}\n\n";

$takenMkIds = \App\Models\KrsDetail::whereHas('krs', function($q) use ($m) {
    $q->where('mahasiswa_id', $m->id);
})->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();

// Query kelas untuk semester genap
$kelasQuery = Kelas::with(['mataKuliah'])
    ->where('tahun_akademik_id', $tahunGenap->id)
    ->where('is_closed', false)
    ->whereHas('mataKuliah', function($q) use ($m, $takenMkIds, $tahunGenap) {
        // Filter paritas semester
        if (strtolower($tahunGenap->semester) === 'ganjil') {
            $q->whereRaw('semester % 2 != 0');
        } else {
            $q->whereRaw('semester % 2 = 0');
        }
        
        // Filter range semester
        $q->where(function($query) use ($m, $takenMkIds) {
            $query->where('semester', $m->semester_sekarang)
                  ->orWhere(function($q2) use ($m, $takenMkIds) {
                      $q2->where('semester', '<', $m->semester_sekarang)
                         ->whereNotIn('mata_kuliah.id', $takenMkIds);
                  });
        });
    });

$kelasCount = $kelasQuery->count();
echo "Total kelas yang lolos query: {$kelasCount} kelas\n";
if ($kelasCount > 0) {
    foreach ($kelasQuery->get() as $k) {
        echo "- Kelas ID: {$k->id} | Nama: {$k->nama} | Matkul: {$k->mataKuliah->nama} (Smt {$k->mataKuliah->semester})\n";
    }
} else {
    echo "⚠️ TIDAK ADA MATA KULIAH YANG LOLOS QUERY!\n";
}
