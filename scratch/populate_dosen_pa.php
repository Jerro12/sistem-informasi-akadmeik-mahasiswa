<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dosen;
use App\Models\Mahasiswa;
use Illuminate\Support\Facades\DB;

echo "=== STARTING DOSEN PA POPULATION ===\n";

DB::transaction(function() {
    // Group dosens by prodi
    $dosensByProdi = Dosen::all()->groupBy('prodi_id');
    $allDosens = Dosen::all();
    
    if ($allDosens->isEmpty()) {
        echo "Error: No Dosen records found in the database. Cannot assign Dosen PA.\n";
        return;
    }

    $defaultDosen = $allDosens->first();
    
    // Store round-robin index counters for each prodi
    $counters = [];

    // Get all mahasiswa with null dosen_pa_id
    $students = Mahasiswa::whereNull('dosen_pa_id')->get();
    echo "Found " . $students->count() . " students without Dosen PA.\n";

    $assignedCount = 0;
    foreach ($students as $student) {
        $prodiId = $student->prodi_id;
        $dosensInProdi = $dosensByProdi->get($prodiId);

        if ($dosensInProdi && $dosensInProdi->isNotEmpty()) {
            // Round-robin selection
            if (!isset($counters[$prodiId])) {
                $counters[$prodiId] = 0;
            }
            $index = $counters[$prodiId] % $dosensInProdi->count();
            $dosen = $dosensInProdi->values()->get($index);
            $counters[$prodiId]++;
        } else {
            // Fallback to default first Dosen in database
            $dosen = $defaultDosen;
        }

        $student->update(['dosen_pa_id' => $dosen->id]);
        $assignedCount++;
    }

    echo "Successfully assigned Dosen PA to {$assignedCount} students.\n";
});

echo "=== DOSEN PA POPULATION COMPLETED ===\n";
