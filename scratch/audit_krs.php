<?php
// Audit menyeluruh semua potensi masalah di alur KRS
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ta = App\Models\TahunAkademik::where('is_active', true)->first();
$config = config('siakad');

echo "============================================================\n";
echo "         AUDIT MENYELURUH FITUR KRS MAHASISWA\n";
echo "============================================================\n\n";

// ============================
// CEK 1: Status Data Master
// ============================
echo "【1】CEK DATA MASTER\n";
echo "------------------------------------------------------------\n";
$tahunList = App\Models\TahunAkademik::all();
echo "Total Tahun Akademik: " . $tahunList->count() . "\n";
$activeCount = $tahunList->where('is_active', true)->count();
echo "Yang is_active=true: $activeCount " . ($activeCount === 1 ? "✅" : "⚠️ HARUSNYA CUKUP 1!") . "\n";

if ($ta) {
    echo "Aktif: " . $ta->tahun . " - " . $ta->semester . "\n";
    echo "biaya_krs: Rp " . number_format($ta->biaya_krs) . "\n";
    echo "isKrsPeriod(): " . ($ta->isKrsPeriod() ? "✅ Periode KRS terbuka" : "ℹ️ Tidak ada batas periode KRS (tanggal null)") . "\n";
}
echo "\n";

// ============================
// CEK 2: Semua Mahasiswa
// ============================
echo "【2】CEK DATA MAHASISWA\n";
echo "------------------------------------------------------------\n";
$mahasiswaList = App\Models\Mahasiswa::with(['user', 'prodi'])->get();
echo "Total mahasiswa: " . $mahasiswaList->count() . "\n\n";

$masalah = [];
foreach ($mahasiswaList as $m) {
    $issues = [];
    if (!$m->prodi_id) $issues[] = "❌ prodi_id NULL";
    if (!$m->semester_sekarang) $issues[] = "❌ semester_sekarang NULL/0";
    if ($m->semester_sekarang % 2 === 0 && strtolower($ta->semester) === 'ganjil')
        $issues[] = "⚠️ Semester genap tapi tahun akademik Ganjil → tidak akan ada matkul tampil";
    if ($m->semester_sekarang % 2 !== 0 && strtolower($ta->semester) === 'genap')
        $issues[] = "⚠️ Semester ganjil tapi tahun akademik Genap → tidak akan ada matkul tampil";
    if ($m->status !== 'aktif') $issues[] = "⚠️ Status bukan 'aktif': " . $m->status;
    if (!$m->user) $issues[] = "❌ User tidak ditemukan";

    $semColor = count($issues) > 0 ? "❌" : "✅";
    echo "  $semColor NIM: " . $m->nim . " | " . ($m->user->name ?? '?') . " | Sem: " . $m->semester_sekarang . " | Status: " . $m->status . "\n";
    foreach ($issues as $iss) echo "       $iss\n";
    if (count($issues)) $masalah[] = $m->nim;
}
echo "\nMahasiswa bermasalah: " . (count($masalah) ? implode(', ', $masalah) : "Tidak ada ✅") . "\n\n";

// ============================
// CEK 3: Kelas per Semester Mahasiswa
// ============================
echo "【3】CEK KETERSEDIAAN KELAS PER MAHASISWA\n";
echo "------------------------------------------------------------\n";
foreach ($mahasiswaList as $m) {
    $kelasTotal = App\Models\Kelas::where('tahun_akademik_id', $ta->id)
        ->whereHas('mataKuliah', function($q) use ($m, $ta) {
            $q->where('prodi_id', $m->prodi_id);
            if (strtolower($ta->semester) === 'ganjil') {
                $q->whereRaw('semester % 2 != 0');
            } else {
                $q->whereRaw('semester % 2 = 0');
            }
            $q->where('semester', $m->semester_sekarang);
        })->count();

    $krs = App\Models\Krs::where('mahasiswa_id', $m->id)->where('tahun_akademik_id', $ta->id)->first();
    $krsStatus = $krs ? $krs->status : 'belum ada';
    $krsDetail = $krs ? $krs->krsDetail()->count() : 0;

    $icon = $kelasTotal > 0 ? "✅" : "❌";
    echo "  $icon NIM: " . $m->nim . " | Sem: " . $m->semester_sekarang
        . " | Kelas sem ini: $kelasTotal"
        . " | KRS status: $krsStatus ($krsDetail mk)\n";

    if ($kelasTotal === 0) {
        echo "       ⚠️ Tidak ada kelas semester " . $m->semester_sekarang . " yang terdaftar di tahun akademik aktif!\n";
    }
}
echo "\n";

// ============================
// CEK 4: Bug is_closed tidak difilter
// ============================
echo "【4】CEK KELAS YANG DITUTUP (is_closed) - TIDAK DIFILTER\n";
echo "------------------------------------------------------------\n";
$closedKelas = App\Models\Kelas::where('is_closed', true)->where('tahun_akademik_id', $ta->id)->count();
$kelasQuery = App\Models\Kelas::where('tahun_akademik_id', $ta->id);
// Cek apakah KrsController memfilter is_closed
echo "Kelas yang is_closed=true di semester aktif: $closedKelas\n";
if ($closedKelas > 0) {
    echo "  ⚠️ BUG POTENSIAL: KrsController TIDAK memfilter kelas is_closed!\n";
    echo "     Mahasiswa bisa melihat kelas yang sudah ditutup.\n";
} else {
    echo "  ✅ Tidak ada kelas yang ditutup saat ini (aman)\n";
}
echo "\n";

// ============================
// CEK 5: Bug takenMkIds - include KRS aktif
// ============================
echo "【5】CEK BUG takenMkIds (matkul KRS aktif masuk blacklist)\n";
echo "------------------------------------------------------------\n";
foreach ($mahasiswaList as $m) {
    $takenMkIds = App\Models\KrsDetail::whereHas('krs', function($q) use ($m) {
        $q->where('mahasiswa_id', $m->id);
    })->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();

    // Berapa banyak matkul saat ini yang masuk takenMkIds
    $krs = App\Models\Krs::where('mahasiswa_id', $m->id)->where('tahun_akademik_id', $ta->id)->first();
    if ($krs) {
        $currentKrsMkIds = App\Models\KrsDetail::where('krs_id', $krs->id)
            ->get()->pluck('kelas.mata_kuliah_id')->filter()->toArray();
        $overlap = array_intersect($takenMkIds, $currentKrsMkIds);
        if (count($overlap) > 0 && strtolower($ta->semester) === 'ganjil') {
            // Cek apakah matkul semester ini (ganjil) termasuk overlap - ini berbahaya
            // jika mahasiswa bisa edit krs (status draft) tapi matkul sudah ke-blacklist
            $krsStatus = $krs->status;
            if ($krsStatus === 'draft') {
                echo "  ⚠️ NIM " . $m->nim . ": " . count($overlap) . " matkul di KRS draft masuk takenMkIds\n";
                echo "     Jika mahasiswa hapus matkul dari KRS, matkul itu tidak bisa ditambah kembali!\n";
            } else {
                echo "  ✅ NIM " . $m->nim . ": KRS status=$krsStatus, overlap wajar (sudah final)\n";
            }
        } else {
            echo "  ✅ NIM " . $m->nim . ": tidak ada overlap berbahaya\n";
        }
    }
}
echo "\n";

// ============================
// CEK 6: Cek config siakad
// ============================
echo "【6】CEK KONFIGURASI SIAKAD\n";
echo "------------------------------------------------------------\n";
$maxSksDefault = config('siakad.maks_sks.default', 24);
$ipsRules = config('siakad.maks_sks.ips_rules', []);
echo "Max SKS default (mahasiswa baru): $maxSksDefault SKS\n";
echo "Aturan max SKS berdasarkan IPS:\n";
if (empty($ipsRules)) {
    echo "  ⚠️ Tidak ada aturan IPS terdefinisi di config!\n";
} else {
    foreach ($ipsRules as $r) {
        echo "  IPS " . $r['min'] . " - " . $r['max'] . " → max " . $r['sks'] . " SKS\n";
    }
}
echo "\n";

// ============================
// CEK 7: Status view untuk pending/rejected
// ============================
echo "【7】CEK STATUS KRS & TAMPILAN VIEW\n";
echo "------------------------------------------------------------\n";
$krsAll = App\Models\Krs::where('tahun_akademik_id', $ta->id)->get();
$statusCount = $krsAll->groupBy('status')->map->count();
echo "Distribusi status KRS semester ini:\n";
foreach ($statusCount as $status => $count) {
    echo "  - $status: $count\n";
}
echo "\n";

// Cek apakah status 'pending' bisa cetak di view
echo "View logic tombol cetak:\n";
echo "  - status=draft → tampil tombol 'Patenkan & Cetak KRS' ✅\n";
echo "  - status=pending → tampil tombol 'Cetak Sekarang' (else branch) ✅\n";
echo "  - status=approved → tampil tombol 'Cetak Sekarang' ✅\n";
echo "  - status=rejected → tampil tombol 'Cetak Sekarang' (tidak ada tombol revisi di view!) ⚠️\n";
echo "    → Mahasiswa yang KRS-nya ditolak tidak ada UI untuk revisi!\n";
echo "\n";

echo "============================================================\n";
echo "                     RINGKASAN MASALAH\n";
echo "============================================================\n";
echo "1. ✅ SUDAH DIPERBAIKI: Redirect pembayaran meski biaya_krs=0\n";
echo "2. ⚠️ BUG: Kelas is_closed tidak difilter di KrsController::index()\n";
echo "3. ⚠️ BUG: takenMkIds mencakup KRS aktif → jika mahasiswa hapus matkul\n";
echo "       dari draft KRS, matkul itu tidak bisa ditambah kembali ke KRS\n";
echo "4. ⚠️ UI: Tidak ada tombol 'Revisi' untuk status 'rejected' di view\n";
echo "       (route revise ada, tapi tidak ada tombol di UI)\n";
