<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;

$tahunAktif = TahunAkademik::where('is_active', true)->first();
if (!$tahunAktif) {
    echo "Tahun Akademik Aktif tidak ditemukan!\n";
    exit;
}

echo "=== DIAGNOSIS MAHASISWA SEMESTER 6 ===\n";
echo "Tahun Akademik Aktif: ID {$tahunAktif->id} | Semester: {$tahunAktif->semester}\n\n";

// Ambil mahasiswa Teknik Informatika (prodi_id = 1) semester 6
$mList = Mahasiswa::with(['user', 'prodi', 'kurikulum'])
    ->where('prodi_id', 1)
    ->where('semester_sekarang', 6)
    ->get();

if ($mList->isEmpty()) {
    echo "Tidak ada mahasiswa Teknik Informatika semester 6 di database!\n";
    // Cari mahasiswa semester 6 di prodi lain
    $mListAll = Mahasiswa::with(['user', 'prodi'])
        ->where('semester_sekarang', 6)
        ->get();
    if ($mListAll->isEmpty()) {
        echo "Bahkan tidak ada mahasiswa semester 6 di seluruh prodi!\n";
        
        // Mari kita cari tahu apakah ada mahasiswa TI dengan semester lain (misal 5 atau 6)?
        $countTI = Mahasiswa::where('prodi_id', 1)->count();
        echo "Total mahasiswa TI di DB: {$countTI}\n";
        $tiSemesters = Mahasiswa::where('prodi_id', 1)->select('semester_sekarang', \DB::raw('count(*) as count'))->groupBy('semester_sekarang')->get();
        foreach ($tiSemesters as $ts) {
            echo "- Semester {$ts->semester_sekarang}: {$ts->count} mahasiswa\n";
        }
    } else {
        echo "Daftar mahasiswa semester 6 di prodi lain:\n";
        foreach ($mListAll as $m) {
            echo "- NIM: {$m->nim} | Nama: {$m->nama} | Prodi: {$m->prodi->nama}\n";
        }
    }
    exit;
}

foreach ($mList as $m) {
    echo "Mahasiswa NIM: {$m->nim} | Nama: {$m->nama}\n";
    
    // Cek target semester
    $isStudentSemesterEven = ($m->semester_sekarang % 2 === 0);
    $isTaSemesterEven = (strtolower($tahunAktif->semester) === 'genap');
    
    $targetSemester = $m->semester_sekarang;
    if ($isStudentSemesterEven !== $isTaSemesterEven) {
        $targetSemester = $m->semester_sekarang + 1;
    }
    
    echo "- Target Semester: {$targetSemester}\n";

    // Cek kelas yang pernah diambil
    $krs = app(\App\Services\KrsService::class)->getActiveKrsOrNew($m);
    $takenMkIds = \App\Models\KrsDetail::whereHas('krs', function($q) use ($m, $krs) {
        $q->where('mahasiswa_id', $m->id)
          ->where('id', '!=', $krs->id);
    })->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();
    
    echo "- Jumlah matkul yang sudah diambil sebelumnya: " . count($takenMkIds) . "\n";
    echo "- Status KRS saat ini: " . ($krs->status ?: 'draft/tidak ada') . "\n";
    echo "- Jumlah matkul di KRS draft saat ini: " . $krs->krsDetail()->count() . "\n";

    // Cek kelas di DB untuk prodi TI (prodi_id = 1) dan tahun akademik ini
    $kelasQuery = Kelas::with(['mataKuliah'])
        ->where('tahun_akademik_id', $tahunAktif->id)
        ->where('is_closed', false)
        ->whereHas('mataKuliah', function($q) use ($m, $takenMkIds, $tahunAktif, $targetSemester) {
            if (strtolower($tahunAktif->semester) === 'ganjil') {
                $q->whereRaw('semester % 2 != 0');
            } else {
                $q->whereRaw('semester % 2 = 0');
            }

            $q->where(function($query) use ($m, $takenMkIds, $targetSemester) {
                $query->where('semester', $targetSemester)
                      ->orWhere(function($q2) use ($m, $takenMkIds, $targetSemester) {
                          $q2->where('semester', '<', $targetSemester)
                             ->whereNotIn('mata_kuliah.id', $takenMkIds);
                      });
            })
            ->where(function($query) use ($m) {
                $query->where('prodi_id', $m->prodi_id)
                      ->orWhereNull('prodi_id');
            });
        });

    $totalKelasFilter = $kelasQuery->count();
    echo "- Jumlah kelas lolos filter: {$totalKelasFilter}\n";
    
    if ($totalKelasFilter > 0) {
        foreach ($kelasQuery->get() as $k) {
            echo "  * [Kelas ID: {$k->id}] {$k->nama} | Matkul: {$k->mataKuliah->nama} (Smt {$k->mataKuliah->semester})\n";
        }
    } else {
        echo "  * ⚠️ TIDAK ADA KELAS DITEMUKAN UNTUK MAHASISWA INI!\n";
        
        // Mari kita cek apakah ada kelas semester 6/genap yang ditawarkan untuk prodi ini
        $allKelasTa = Kelas::where('tahun_akademik_id', $tahunAktif->id)
            ->whereHas('mataKuliah', function($q) use ($m) {
                $q->where('prodi_id', $m->prodi_id);
            })->get();
        echo "  * Total kelas terdaftar di TA ini untuk Prodi TI: " . $allKelasTa->count() . "\n";
        foreach ($allKelasTa as $ak) {
            echo "    - [ID: {$ak->id}] {$ak->nama} | Matkul: {$ak->mataKuliah->nama} (Smt {$ak->mataKuliah->semester}) | Closed: " . ($ak->is_closed ? 'YA' : 'TIDAK') . "\n";
        }
    }
}
