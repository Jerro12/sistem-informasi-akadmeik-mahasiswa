<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Kelas;
use App\Models\MataKuliah;

$tahunId = 3;
$prodiId = 2;

echo "Classes for Prodi 2 in Tahun ID 3:\n";
$kelas = Kelas::where('tahun_akademik_id', $tahunId)
    ->whereHas('mataKuliah', function($q) use ($prodiId) {
        $q->where('prodi_id', $prodiId);
    })->with('mataKuliah')->get();

if ($kelas->count() > 0) {
    foreach ($kelas as $k) {
        echo "- " . $k->mataKuliah->nama_mk . " | Semester: " . $k->mataKuliah->semester . "\n";
    }
} else {
    echo "No classes found for Prodi 2.\n";
    
    echo "\nMata Kuliah existing for Prodi 2:\n";
    $mks = MataKuliah::where('prodi_id', $prodiId)->get();
    foreach ($mks as $mk) {
        echo "- " . $mk->nama_mk . " | Semester: " . $mk->semester . "\n";
    }
}
