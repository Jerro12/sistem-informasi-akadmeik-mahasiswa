<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use App\Models\Mahasiswa;

echo "=== DAFTAR SEMUA USER DI DATABASE ===\n";
$users = User::all();
foreach ($users as $u) {
    echo "- ID: {$u->id} | Name: {$u->name} | Email: {$u->email} | Role: {$u->role}\n";
    if ($u->role === 'mahasiswa') {
        $m = Mahasiswa::where('user_id', $u->id)->first();
        if ($m) {
            echo "  -> Terhubung ke Mahasiswa NIM: {$m->nim} | Nama: {$m->nama} | Semester: {$m->semester_sekarang} | Prodi ID: {$m->prodi_id}\n";
        } else {
            echo "  -> ⚠️ ERROR: Tidak ada profil mahasiswa terhubung!\n";
        }
    }
}
