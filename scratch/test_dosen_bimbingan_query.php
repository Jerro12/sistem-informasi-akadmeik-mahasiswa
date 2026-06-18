<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Dosen;

$dosen = Dosen::first();
if (!$dosen) {
    echo "Dosen tidak ditemukan!\n";
    exit;
}

echo "=== TESTING DOSEN MAHASISWA BIMBINGAN QUERY ===\n";
echo "Dosen ID: {$dosen->id}\n\n";

$sortOptions = ['nama', 'nim', 'angkatan', 'status'];
$searchOptions = ['', 'Budi'];

foreach ($sortOptions as $sort) {
    foreach ($searchOptions as $search) {
        echo "Testing Sort: {$sort} | Search: '{$search}'\n";
        try {
            $query = $dosen->mahasiswaBimbingan()
                ->with(['user', 'prodi', 'krs' => fn($q) => $q->latest()]);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nim', 'like', "%{$search}%")
                        ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$search}%"));
                });
            }

            $sortDir = 'asc';
            if ($sort === 'nama') {
                $query->join('users', 'mahasiswa.user_id', '=', 'users.id')
                      ->orderBy('users.name', $sortDir)
                      ->select('mahasiswa.*');
            } elseif (in_array($sort, ['nim', 'angkatan', 'status'])) {
                $query->orderBy($sort, $sortDir);
            }

            // Run get to execute SQL
            $results = $query->get();
            echo "- SUCCESS: found " . $results->count() . " rows.\n";
        } catch (\Exception $e) {
            echo "- ERROR: " . $e->getMessage() . "\n";
        }
        echo "---------------------------------\n";
    }
}
