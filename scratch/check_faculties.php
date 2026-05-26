<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

foreach (App\Models\Fakultas::all() as $f) {
    echo "ID: " . $f->id . " | Nama: " . $f->nama . "\n";
}
