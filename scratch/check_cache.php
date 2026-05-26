<?php

require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Services\AkademikService;

$ta_db = TahunAkademik::where('is_active', true)->first();
$ta_service = app(AkademikService::class)->getActiveTahun();

echo "DB Active TA ID: " . ($ta_db?->id ?? 'null') . "\n";
echo "Service Active TA ID: " . ($ta_service?->id ?? 'null') . "\n";

if ($ta_db && $ta_service && $ta_db->id != $ta_service->id) {
    echo "ALERT: Cache is stale! DB and Service return different active TA.\n";
} else {
    echo "Cache is consistent.\n";
}
