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

$prodis = App\Models\Prodi::all();
$tahunId = 3;

echo "Kelas counts per Prodi (Tahun ID $tahunId):\n";
foreach ($prodis as $p) {
    $count = App\Models\Kelas::where('tahun_akademik_id', $tahunId)
        ->whereHas('mataKuliah', function($q) use ($p) {
            $q->where('prodi_id', $p->id);
        })->count();
    echo $p->nama . " (ID: " . $p->id . "): " . $count . "\n";
}
