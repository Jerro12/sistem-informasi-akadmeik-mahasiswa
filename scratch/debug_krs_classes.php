<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;

echo "--- DIAGNOSIS DATA KRS & KALENDER AKADEMIK ---\n\n";

// 1. Cek Tahun Akademik Aktif
$tahunAktif = TahunAkademik::where('is_active', true)->first();
if ($tahunAktif) {
    echo "Tahun Akademik Aktif:\n";
    echo "- ID: {$tahunAktif->id}\n";
    echo "- Nama: {$tahunAktif->nama}\n";
    echo "- Semester: {$tahunAktif->semester}\n";
    echo "- Status Aktif: " . ($tahunAktif->is_active ? 'Ya' : 'Tidak') . "\n";
    echo "- Biaya KRS: {$tahunAktif->biaya_krs}\n\n";
} else {
    echo "Peringatan: TIDAK ADA Tahun Akademik yang aktif!\n\n";
}

// 2. Cek Total Kelas di DB untuk Tahun Akademik Aktif
if ($tahunAktif) {
    $totalKelasAktif = Kelas::where('tahun_akademik_id', $tahunAktif->id)->count();
    $totalKelasClosed = Kelas::where('tahun_akademik_id', $tahunAktif->id)->where('is_closed', true)->count();
    $totalKelasOpen = Kelas::where('tahun_akademik_id', $tahunAktif->id)->where('is_closed', false)->count();
    echo "Data Kelas pada Tahun Akademik Aktif:\n";
    echo "- Total Kelas: {$totalKelasAktif}\n";
    echo "- Kelas Terbuka: {$totalKelasOpen}\n";
    echo "- Kelas Tertutup: {$totalKelasClosed}\n\n";
} else {
    $totalKelas = Kelas::count();
    echo "Total seluruh kelas di database: {$totalKelas}\n\n";
}

// 3. Cek sampel data mahasiswa
$mahasiswaList = Mahasiswa::with('user', 'prodi')->take(5)->get();
echo "Sampel Data Mahasiswa:\n";
foreach ($mahasiswaList as $m) {
    echo "- NIM: {$m->nim} | Nama: {$m->nama} | Prodi: " . ($m->prodi?->nama ?? 'Tidak ada') . " | Semester: {$m->semester_sekarang} | User Email: " . ($m->user?->email ?? 'Tidak ada') . "\n";
    
    // Cek apa saja kelas yang tersedia untuk mahasiswa ini jika dia login
    $takenMkIds = \App\Models\KrsDetail::whereHas('krs', function($q) use ($m) {
        $q->where('mahasiswa_id', $m->id);
    })->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();

    if ($tahunAktif) {
        $kelasQuery = Kelas::with(['mataKuliah'])
            ->where('tahun_akademik_id', $tahunAktif->id)
            ->where('is_closed', false)
            ->whereHas('mataKuliah', function($q) use ($m, $takenMkIds, $tahunAktif) {
                if (strtolower($tahunAktif->semester) === 'ganjil') {
                    $q->whereRaw('semester % 2 != 0');
                } else {
                    $q->whereRaw('semester % 2 = 0');
                }
                
                $q->where(function($query) use ($m, $takenMkIds) {
                    $query->where('semester', $m->semester_sekarang)
                          ->orWhere(function($q2) use ($m, $takenMkIds) {
                              $q2->where('semester', '<', $m->semester_sekarang)
                                 ->whereNotIn('mata_kuliah.id', $takenMkIds);
                          });
                })
                ->where(function($query) use ($m) {
                    $query->where('prodi_id', $m->prodi_id)
                          ->orWhereNull('prodi_id');
                });
            });
            
        $kelasCount = $kelasQuery->count();
        echo "  -> Kelas yang lolos filter KRS untuk mahasiswa ini: {$kelasCount} kelas\n";
        if ($kelasCount > 0) {
            $sampleK = $kelasQuery->take(3)->get();
            foreach ($sampleK as $sk) {
                echo "     * [ID: {$sk->id}] Kelas: {$sk->nama} | Matkul: {$sk->mataKuliah->nama} (Smt {$sk->mataKuliah->semester})\n";
            }
        } else {
            // Analisis kenapa 0
            // Cari tahu apakah ada matkul untuk prodi ini
            $totalMatkulProdi = MataKuliah::where('prodi_id', $m->prodi_id)->count();
            echo "     * Info: Prodi ini memiliki {$totalMatkulProdi} mata kuliah.\n";
            if ($totalMatkulProdi > 0) {
                $matkulProdi = MataKuliah::where('prodi_id', $m->prodi_id)->get();
                echo "     * Semester ganjil/genap mismatch atau semester sekarang mismatch?\n";
                echo "       Semester Aktif TA: {$tahunAktif->semester}\n";
                echo "       Semester Sekarang Mhs: {$m->semester_sekarang}\n";
                // Cek apakah ada kelas untuk prodi ini secara umum
                $kelasProdi = Kelas::whereHas('mataKuliah', function($q) use ($m) {
                    $q->where('prodi_id', $m->prodi_id);
                })->count();
                echo "       Total kelas terdaftar untuk prodi mahasiswa ini: {$kelasProdi} kelas\n";
            }
        }
    }
}
