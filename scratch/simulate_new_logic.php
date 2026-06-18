<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\TahunAkademik;
use App\Models\Kelas;
use App\Models\Mahasiswa;

// Let's test combinations
$combinations = [
    ['student_semester' => 5, 'ta_semester' => 'Ganjil', 'ta_id' => 3], // Budi in Ganjil (matches)
    ['student_semester' => 5, 'ta_semester' => 'Genap', 'ta_id' => 2],  // Budi in Genap (mismatch)
    ['student_semester' => 4, 'ta_semester' => 'Ganjil', 'ta_id' => 3], // Student in 4 entering Ganjil (mismatch)
    ['student_semester' => 4, 'ta_semester' => 'Genap', 'ta_id' => 2],  // Student in 4 entering Genap (matches)
];

echo "=== VERIFIKASI LOGIKA BARU TARGET SEMESTER ===\n\n";

foreach ($combinations as $c) {
    $sem = $c['student_semester'];
    $taSem = $c['ta_semester'];
    $taId = $c['ta_id'];

    // Hitung target semester
    $isStudentSemesterEven = ($sem % 2 === 0);
    $isTaSemesterEven = (strtolower($taSem) === 'genap');
    
    $targetSemester = $sem;
    if ($isStudentSemesterEven !== $isTaSemesterEven) {
        $targetSemester = $sem + 1;
    }

    echo "Skenario:\n";
    echo "- Semester Mhs: {$sem} | Semester TA: {$taSem}\n";
    echo "- Target Semester dihitung: {$targetSemester}\n";

    // Simulasi Query
    $takenMkIds = []; // Asumsikan belum pernah ambil agar melihat semua potensial kelas
    
    $kelasQuery = Kelas::with(['mataKuliah'])
        ->where('tahun_akademik_id', $taId)
        ->where('is_closed', false)
        ->whereHas('mataKuliah', function($q) use ($sem, $takenMkIds, $taSem, $targetSemester) {
            // Filter paritas semester
            if (strtolower($taSem) === 'ganjil') {
                $q->whereRaw('semester % 2 != 0');
            } else {
                $q->whereRaw('semester % 2 = 0');
            }
            
            // Filter range semester menggunakan targetSemester
            $q->where(function($query) use ($targetSemester, $takenMkIds) {
                $query->where('semester', $targetSemester)
                      ->orWhere(function($q2) use ($targetSemester, $takenMkIds) {
                          $q2->where('semester', '<', $targetSemester)
                             ->whereNotIn('mata_kuliah.id', $takenMkIds);
                      });
            });
        });

    $count = $kelasQuery->count();
    echo "- Hasil Query: {$count} kelas ditemukan\n";
    if ($count > 0) {
        $sample = $kelasQuery->take(2)->get();
        foreach ($sample as $k) {
            echo "  * [Kelas ID: {$k->id}] {$k->nama} (Smt {$k->mataKuliah->semester})\n";
        }
    } else {
        echo "  * ⚠️ TIDAK ADA KELAS DITEMUKAN!\n";
    }
    echo "-----------------------------------------\n";
}
