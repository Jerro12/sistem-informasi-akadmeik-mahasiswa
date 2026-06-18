<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;

$tahunAktif = TahunAkademik::where('is_active', true)->first();
if (!$tahunAktif) {
    echo "ERROR: Tidak ada Tahun Akademik yang aktif saat ini!\n";
    exit(1);
}

echo "=== DETAIL PENELITIAN KETERSEDIAAN MATKUL KRS MAHASISWA ===\n";
echo "Tahun Akademik Aktif: ID {$tahunAktif->id} | Semester: {$tahunAktif->semester}\n\n";

$students = Mahasiswa::with(['user', 'prodi', 'kurikulum'])->get();

foreach ($students as $m) {
    echo "=========================================\n";
    echo "Mahasiswa NIM: {$m->nim} | Nama: {$m->nama}\n";
    echo "Prodi: " . ($m->prodi?->nama ?? 'N/A') . " (ID: {$m->prodi_id})\n";
    echo "Kurikulum: " . ($m->kurikulum?->nama ?? 'N/A') . " (ID: {$m->kurikulum_id})\n";
    echo "Semester Sekarang: {$m->semester_sekarang}\n";
    
    // Check semester parity mismatch
    $isSmtEven = ($m->semester_sekarang % 2 == 0);
    $isTaEven = (strtolower($tahunAktif->semester) === 'genap');
    if ($isSmtEven !== $isTaEven) {
        echo "⚠️ MISMATCH SEMESTER: Mahasiswa berada di Semester " . ($isSmtEven ? 'GENAP' : 'GANJIL') . " ({$m->semester_sekarang}), tetapi Tahun Akademik Aktif adalah " . ($isTaEven ? 'GENAP' : 'GANJIL') . ".\n";
        echo "   Hal ini menyebabkan mata kuliah semester {$m->semester_sekarang} tidak akan muncul karena terfilter aturan ganjil/genap!\n";
    }

    $krs = \App\Services\KrsService::class;
    $krsService = app($krs);
    $activeKrs = $krsService->getActiveKrsOrNew($m);
    echo "Status KRS Saat Ini: " . ($activeKrs->status ?: 'draft/tidak ada') . "\n";
    if ($activeKrs->konsentrasi_id) {
        echo "Konsentrasi Terpilih: ID {$activeKrs->konsentrasi_id}\n";
    } else {
        echo "Konsentrasi Terpilih: Belum Memilih\n";
    }

    // Check taken class IDs
    $takenMkIds = \App\Models\KrsDetail::whereHas('krs', function($q) use ($m, $activeKrs) {
        $q->where('mahasiswa_id', $m->id)
          ->where('id', '!=', $activeKrs->id);
    })->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();
    echo "Matkul yang sudah diambil di KRS sebelumnya (IDs): [" . implode(', ', $takenMkIds) . "]\n";

    // Let's run queries step by step to see where the count becomes 0
    
    // Step A: All classes in this active academic year
    $qA = Kelas::where('tahun_akademik_id', $tahunAktif->id);
    echo "1. Total kelas di Tahun Akademik aktif: " . $qA->count() . "\n";

    // Step B: Open classes
    $qB = (clone $qA)->where('is_closed', false);
    echo "2. Kelas terbuka (is_closed = false): " . $qB->count() . "\n";

    // Step C: Filter by Prodi & Null Prodi
    $qC = (clone $qB)->whereHas('mataKuliah', function($q) use ($m) {
        $q->where(function($query) use ($m) {
            $query->where('prodi_id', $m->prodi_id)
                  ->orWhereNull('prodi_id');
        });
    });
    echo "3. Kelas dengan Prodi cocok/umum: " . $qC->count() . "\n";

    // Step D: Filter Semester Parity
    $qD = (clone $qC)->whereHas('mataKuliah', function($q) use ($tahunAktif) {
        if (strtolower($tahunAktif->semester) === 'ganjil') {
            $q->whereRaw('semester % 2 != 0');
        } else {
            $q->whereRaw('semester % 2 = 0');
        }
    });
    echo "4. Kelas setelah filter ganjil/genap: " . $qD->count() . "\n";

    // Step E: Filter Semester (Semester Sekarang atau Semester Bawah belum diambil)
    $qE = (clone $qD)->whereHas('mataKuliah', function($q) use ($m, $takenMkIds) {
        $q->where(function($query) use ($m, $takenMkIds) {
            $query->where('semester', $m->semester_sekarang)
                  ->orWhere(function($q2) use ($m, $takenMkIds) {
                      $q2->where('semester', '<', $m->semester_sekarang)
                         ->whereNotIn('mata_kuliah.id', $takenMkIds);
                  });
        });
    });
    echo "5. Kelas setelah filter semester sekarang/bawah: " . $qE->count() . "\n";

    // Step F: Filter Kurikulum
    $qF = (clone $qE);
    if ($m->kurikulum_id) {
        $qF = $qF->whereHas('mataKuliah', function($q) use ($m) {
            $q->where(function($query) use ($m) {
                $query->where('kurikulum_id', $m->kurikulum_id)
                      ->orWhereNull('kurikulum_id');
            });
        });
    }
    echo "6. Kelas setelah filter kurikulum: " . $qF->count() . "\n";

    // Step G: Filter Konsentrasi
    $qG = (clone $qF);
    if ($activeKrs->konsentrasi_id) {
        $qG = $qG->whereHas('mataKuliah', function($q) use ($activeKrs) {
            $q->where(function($query) use ($activeKrs) {
                $query->where('konsentrasi_id', $activeKrs->konsentrasi_id)
                      ->orWhereNull('konsentrasi_id');
            });
        });
    } else {
        $qG = $qG->whereHas('mataKuliah', function($q) {
            $q->whereNull('konsentrasi_id');
        });
    }
    echo "7. Kelas setelah filter konsentrasi: " . $qG->count() . "\n";

    // Step H: Filter yang sudah dimasukkan ke KRS aktif saat ini
    $qH = (clone $qG)->whereDoesntHave('krsDetail', function($q) use ($activeKrs) {
        $q->where('krs_id', $activeKrs->id);
    });
    echo "8. Kelas final yang tersedia di form pemilihan KRS: " . $qH->count() . "\n";

    if ($qH->count() > 0) {
        echo "   -> List Matkul yang muncul:\n";
        foreach ($qH->get() as $k) {
            echo "      - Kelas [ID: {$k->id}]: {$k->nama} | Matkul: {$k->mataKuliah->nama} (Smt {$k->mataKuliah->semester}) | Konsentrasi: " . ($k->mataKuliah->konsentrasi_id ?: 'Umum') . "\n";
        }
    } else {
        echo "   -> ⚠️ TIDAK ADA MATKUL YANG MUNCUL!\n";
    }
}
