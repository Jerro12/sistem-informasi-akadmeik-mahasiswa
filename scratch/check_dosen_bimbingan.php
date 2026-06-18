<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Dosen;
use App\Models\Mahasiswa;

echo "=== DOSEN ACCOUNTS ===\n";
$dosens = Dosen::with('user')->get();
foreach ($dosens as $d) {
    $mCount = $d->mahasiswaBimbingan()->count();
    echo "ID: {$d->id} | Name: {$d->user->name} | Email: {$d->user->email} | Role: {$d->user->role} | Supervised Students: {$mCount}\n";
}

echo "\n=== MAHASISWA WITHOUT DOSEN PA ===\n";
$mNoPa = Mahasiswa::with('user')->whereNull('dosen_pa_id')->get();
echo "Count: " . $mNoPa->count() . "\n";
foreach ($mNoPa as $m) {
    echo "NIM: {$m->nim} | Name: {$m->user->name} | Prodi ID: {$m->prodi_id}\n";
}

echo "\n=== MAHASISWA WITH DOSEN PA ===\n";
$mWithPa = Mahasiswa::with(['user', 'dosenPa.user'])->whereNotNull('dosen_pa_id')->get();
echo "Count: " . $mWithPa->count() . "\n";
foreach ($mWithPa as $m) {
    echo "NIM: {$m->nim} | Name: {$m->user->name} | Dosen PA: {$m->dosenPa->user->name} (ID: {$m->dosen_pa_id})\n";
}
