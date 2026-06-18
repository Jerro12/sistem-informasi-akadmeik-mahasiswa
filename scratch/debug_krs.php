<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$mahasiswa = App\Models\Mahasiswa::with(['user'])->first();
$ta = App\Models\TahunAkademik::where('is_active', true)->first();

echo "=== TAHUN AKADEMIK AKTIF ===\n";
echo "ID: " . $ta->id . "\n";
echo "Tahun: " . $ta->tahun . "\n";
echo "Semester: " . $ta->semester . "\n";
echo "Biaya KRS: " . $ta->biaya_krs . "\n";
echo "is_active: " . ($ta->is_active ? 'ya' : 'tidak') . "\n";
echo "isKrsPeriod(): " . ($ta->isKrsPeriod() ? 'ya (periode KRS terbuka)' : 'tidak (periode KRS TERTUTUP)') . "\n";
echo "\n";

echo "=== DATA MAHASISWA ===\n";
if ($mahasiswa) {
    echo "NIM: " . $mahasiswa->nim . "\n";
    echo "Nama: " . $mahasiswa->user->name . "\n";
    echo "Semester: " . $mahasiswa->semester_sekarang . "\n";
    echo "Status: " . $mahasiswa->status . "\n";
    echo "Prodi ID: " . $mahasiswa->prodi_id . "\n";
    echo "\n";

    echo "=== CEK PEMBAYARAN ===\n";
    $pembayaran = App\Models\Pembayaran::where('mahasiswa_id', $mahasiswa->id)
        ->where('tahun_akademik_id', $ta->id)
        ->get();
    echo "Total record pembayaran semester ini: " . $pembayaran->count() . "\n";
    foreach ($pembayaran as $p) {
        echo "  - Status: " . $p->status . ", Amount: " . $p->amount . "\n";
    }
    $isPaid = App\Models\Pembayaran::where('mahasiswa_id', $mahasiswa->id)
        ->where('tahun_akademik_id', $ta->id)
        ->where('status', 'success')
        ->exists();
    echo "isPaid (status=success): " . ($isPaid ? 'YA' : 'TIDAK - ini kemungkinan penyebab redirect ke pembayaran') . "\n";
    echo "\n";

    echo "=== KRS MAHASISWA ===\n";
    $krs = App\Models\Krs::where('mahasiswa_id', $mahasiswa->id)
        ->where('tahun_akademik_id', $ta->id)
        ->first();
    if ($krs) {
        echo "KRS ID: " . $krs->id . "\n";
        echo "Status KRS: " . $krs->status . "\n";
        echo "Jumlah detail: " . $krs->krsDetail()->count() . "\n";
    } else {
        echo "Belum ada KRS untuk semester ini\n";
    }
    echo "\n";

    echo "=== KELAS TERSEDIA ===\n";
    $kelasCount = App\Models\Kelas::where('tahun_akademik_id', $ta->id)->count();
    echo "Jumlah kelas di semester ini: " . $kelasCount . "\n";
} else {
    echo "Tidak ada data mahasiswa!\n";
}
