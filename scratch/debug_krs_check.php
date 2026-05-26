<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;

// Find a student in Prodi 1 (Teknik Informatika) Semester 1
$mahasiswa = Mahasiswa::where('prodi_id', 1)->where('semester_sekarang', 1)->first();
if (!$mahasiswa) {
    echo "No student found.\n";
    exit;
}

$tahunAktif = TahunAkademik::where('is_active', true)->first();

$krsService = app(\App\Services\KrsService::class);
$krs = $krsService->getActiveKrsOrNew($mahasiswa);

$takenMkIds = KrsDetail::whereHas('krs', function($q) use ($mahasiswa) {
    $q->where('mahasiswa_id', $mahasiswa->id);
})->get()->pluck('kelas.mata_kuliah_id')->unique()->toArray();

$availableKelas = Kelas::with(['mataKuliah', 'dosen.user', 'krsDetail'])
    ->where('tahun_akademik_id', $tahunAktif?->id) 
    ->whereHas('mataKuliah', function($q) use ($mahasiswa, $takenMkIds, $krs) {
        $q->where(function($query) use ($mahasiswa, $takenMkIds) {
            $query->where('semester', $mahasiswa->semester_sekarang)
                  ->orWhere(function($q2) use ($mahasiswa, $takenMkIds) {
                      $q2->where('semester', '<', $mahasiswa->semester_sekarang)
                         ->whereNotIn('mata_kuliah.id', $takenMkIds);
                  });
        })
        ->where(function($query) use ($mahasiswa) {
            $query->where('prodi_id', $mahasiswa->prodi_id)
                  ->orWhereNull('prodi_id');
        });
        
        if ($mahasiswa->kurikulum_id) {
            $q->where(function($query) use ($mahasiswa) {
                $query->where('kurikulum_id', $mahasiswa->kurikulum_id)
                      ->orWhereNull('kurikulum_id');
            });
        }
        
        if ($krs->konsentrasi_id) {
            $q->where(function($query) use ($krs) {
                $query->where('konsentrasi_id', $krs->konsentrasi_id)
                      ->orWhereNull('konsentrasi_id');
            });
        } else {
            $q->whereNull('konsentrasi_id');
        }
    })
    ->whereDoesntHave('krsDetail', function($q) use ($krs) {
        $q->where('krs_id', $krs->id);
    })
    ->get()
    ->groupBy(fn($k) => 'Semester ' . $k->mataKuliah->semester);

echo "Mahasiswa NIM: {$mahasiswa->nim} | Prodi: {$mahasiswa->prodi_id} | Semester: {$mahasiswa->semester_sekarang}\n";
echo "KRS Status: {$krs->status} | Konsentrasi ID: " . ($krs->konsentrasi_id ?? 'None') . "\n";
echo "Available Kelas Groups:\n";

if ($availableKelas->isEmpty()) {
    echo "- Tidak ada kelas tersedia\n";
} else {
    foreach ($availableKelas as $semester => $kelasList) {
        echo "[$semester]\n";
        foreach ($kelasList as $kelas) {
            echo "  - {$kelas->mataKuliah->nama_mk} ({$kelas->mataKuliah->sks} SKS) - Dosen: " . ($kelas->dosen->user->name ?? '-') . "\n";
        }
    }
}
