<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahasiswa = App\Models\Mahasiswa::with(['user', 'prodi'])->first();
$ta = App\Models\TahunAkademik::where('is_active', true)->first();

echo "=== INFO MAHASISWA ===\n";
echo "NIM: " . $mahasiswa->nim . "\n";
echo "Nama: " . $mahasiswa->user->name . "\n";
echo "Semester Sekarang: " . $mahasiswa->semester_sekarang . "\n";
echo "Prodi ID: " . $mahasiswa->prodi_id . "\n";
echo "Kurikulum ID: " . ($mahasiswa->kurikulum_id ?? 'NULL') . "\n";
echo "\n";

echo "=== TAHUN AKADEMIK AKTIF ===\n";
echo "Tahun: " . $ta->tahun . " - " . $ta->semester . "\n";
echo "\n";

echo "=== KELAS DI SEMESTER AKTIF ===\n";
$allKelas = App\Models\Kelas::with(['mataKuliah', 'dosen.user'])
    ->where('tahun_akademik_id', $ta->id)
    ->get();
echo "Total kelas: " . $allKelas->count() . "\n\n";

foreach ($allKelas as $k) {
    echo "  - [" . $k->mataKuliah->kode_mk . "] " . $k->mataKuliah->nama_mk 
        . " | Sem: " . $k->mataKuliah->semester 
        . " | SKS: " . $k->mataKuliah->sks
        . " | Prodi ID: " . ($k->mataKuliah->prodi_id ?? 'NULL')
        . " | Kurikulum ID: " . ($k->mataKuliah->kurikulum_id ?? 'NULL')
        . " | Konsentrasi ID: " . ($k->mataKuliah->konsentrasi_id ?? 'NULL')
        . "\n";
}
echo "\n";

echo "=== KELAS YANG SEHARUSNYA MUNCUL UNTUK MAHASISWA ===\n";
echo "(Semester mahasiswa: " . $mahasiswa->semester_sekarang . ", Prodi: " . $mahasiswa->prodi_id . ")\n\n";

// Replicate logic from KrsController
$krs = App\Models\Krs::firstOrCreate(
    ['mahasiswa_id' => $mahasiswa->id, 'tahun_akademik_id' => $ta->id],
    ['status' => 'draft']
);

$takenMkIds = App\Models\KrsDetail::whereHas('krs', function($q) use ($mahasiswa) {
    $q->where('mahasiswa_id', $mahasiswa->id);
})->get()->pluck('kelas.mata_kuliah_id')->unique()->toArray();

$availableKelas = App\Models\Kelas::with(['mataKuliah', 'dosen.user', 'krsDetail'])
    ->where('tahun_akademik_id', $ta->id)
    ->whereHas('mataKuliah', function($q) use ($mahasiswa, $takenMkIds, $krs, $ta) {
        if ($ta) {
            if (strtolower($ta->semester) === 'ganjil') {
                $q->whereRaw('semester % 2 != 0');
            } else {
                $q->whereRaw('semester % 2 = 0');
            }
        }

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
    ->get();

echo "Kelas yang tampil di KRS mahasiswa: " . $availableKelas->count() . "\n";
if ($availableKelas->count() > 0) {
    foreach ($availableKelas as $k) {
        echo "  ✅ [" . $k->mataKuliah->kode_mk . "] " . $k->mataKuliah->nama_mk 
            . " | Sem: " . $k->mataKuliah->semester 
            . " | SKS: " . $k->mataKuliah->sks . "\n";
    }
} else {
    echo "  ❌ TIDAK ADA kelas yang tampil!\n";
    echo "\n=== DIAGNOSA PENYEBAB ===\n";

    // Cek apakah ada matkul prodi ini
    $mkProdi = App\Models\MataKuliah::where('prodi_id', $mahasiswa->prodi_id)->count();
    echo "Matkul dengan prodi_id=" . $mahasiswa->prodi_id . ": " . $mkProdi . "\n";

    // Cek apakah ada matkul ganjil/genap
    if (strtolower($ta->semester) === 'ganjil') {
        $mkGanjil = App\Models\MataKuliah::whereRaw('semester % 2 != 0')->where('prodi_id', $mahasiswa->prodi_id)->count();
        echo "Matkul ganjil untuk prodi ini: " . $mkGanjil . "\n";
    } else {
        $mkGenap = App\Models\MataKuliah::whereRaw('semester % 2 = 0')->where('prodi_id', $mahasiswa->prodi_id)->count();
        echo "Matkul genap untuk prodi ini: " . $mkGenap . "\n";
    }

    // Cek kurikulum
    if ($mahasiswa->kurikulum_id) {
        $mkKurikulum = App\Models\MataKuliah::where('kurikulum_id', $mahasiswa->kurikulum_id)->count();
        echo "Matkul dengan kurikulum_id=" . $mahasiswa->kurikulum_id . ": " . $mkKurikulum . "\n";
    }

    // Cek konsentrasi
    echo "Konsentrasi KRS saat ini: " . ($krs->konsentrasi_id ?? 'NULL (hanya tampil matkul tanpa konsentrasi)') . "\n";

    // Cek sudah diambil di KRS ini
    $alreadyInKrs = App\Models\KrsDetail::where('krs_id', $krs->id)->count();
    echo "Matkul sudah di KRS ini: " . $alreadyInKrs . "\n";
}

echo "\n=== KRS SAAT INI ===\n";
echo "Status: " . $krs->status . "\n";
$details = $krs->krsDetail()->with(['kelas.mataKuliah'])->get();
echo "Matkul di KRS (" . $details->count() . "):\n";
foreach ($details as $d) {
    echo "  - " . $d->kelas->mataKuliah->nama_mk . " (" . $d->kelas->mataKuliah->sks . " SKS)\n";
}
