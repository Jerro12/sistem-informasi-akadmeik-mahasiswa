<?php

namespace App\Services;

use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Krs;
use App\Models\TahunAkademik;
use Illuminate\Support\Collection;

class AkademikCalculationService
{
    /**
     * Calculate IPS (Indeks Prestasi Semester) for a specific semester
     */
    public function calculateIPS(Mahasiswa $mahasiswa, ?int $tahunAkademikId = null): array
    {
        $tahunAkademikId = $tahunAkademikId ?? TahunAkademik::where('is_active', true)->first()?->id;
        
        if (!$tahunAkademikId) {
            return ['ips' => 0, 'ipk' => 0, 'total_sks' => 0, 'total_sks_lulus' => 0, 'total_bobot' => 0];
        }

        // Get approved KRS for this semester
        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademikId)
            ->where('status', 'approved')
            ->with(['krsDetail.kelas.mataKuliah'])
            ->first();

        if ($krs && $krs->krsDetail->isNotEmpty()) {
            $nilaiMap = Nilai::where('mahasiswa_id', $mahasiswa->id)->get()->keyBy('kelas_id');
            
            $totalSks = 0;
            $totalSksLulus = 0;
            $totalBobot = 0;

            foreach ($krs->krsDetail as $detail) {
                $mk = $detail->kelas?->mataKuliah;
                if (!$mk) continue;

                $sks = (int) $mk->sks;
                $totalSks += $sks;

                $nilaiObj = $nilaiMap->get($detail->kelas_id);
                $huruf = $nilaiObj && $nilaiObj->nilai_huruf ? strtoupper(trim($nilaiObj->nilai_huruf)) : '';
                $bobot = ($huruf !== '' && $huruf !== '-' && $huruf !== 'T') ? $this->getBobot($huruf) : 0.0;

                if ($huruf !== '' && $huruf !== '-' && $huruf !== 'T') {
                    $totalBobot += ($sks * $bobot);

                    if (in_array($huruf, ['A', 'B+', 'B', 'C+', 'C', 'D'])) {
                        $totalSksLulus += $sks;
                    }
                }
            }

            $ips = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0.00;

            return [
                'ips' => $ips,
                'ipk' => $ips,
                'total_sks' => $totalSks,
                'total_sks_lulus' => $totalSksLulus,
                'total_bobot' => $totalBobot,
            ];
        }

        $nilaiList = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->where(function ($query) use ($tahunAkademikId, $mahasiswa) {
                $query->whereHas('kelas', function ($q) use ($tahunAkademikId) {
                    $q->where('tahun_akademik_id', $tahunAkademikId);
                })
                ->orWhereHas('kelas.krsDetail', function ($q) use ($tahunAkademikId, $mahasiswa) {
                    $q->whereHas('krs', function ($q2) use ($tahunAkademikId, $mahasiswa) {
                        $q2->where('tahun_akademik_id', $tahunAkademikId)
                           ->where('mahasiswa_id', $mahasiswa->id);
                    });
                });
            })
            ->with('kelas.mataKuliah')
            ->get();

        return $this->calculateIndexFromNilai($nilaiList);
    }

    /**
     * Calculate IPK (Indeks Prestasi Kumulatif) - all semesters
     */
    public function calculateIPK(Mahasiswa $mahasiswa): array
    {
        $krsList = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'approved')
            ->with(['krsDetail.kelas.mataKuliah'])
            ->get();

        if ($krsList->isNotEmpty()) {
            $nilaiMap = Nilai::where('mahasiswa_id', $mahasiswa->id)->get()->keyBy('kelas_id');
            
            // Group by unique mata_kuliah_id to take best grade if course was repeated
            $groupedByMk = [];
            
            foreach ($krsList as $krs) {
                foreach ($krs->krsDetail as $detail) {
                    $mk = $detail->kelas?->mataKuliah;
                    if (!$mk) continue;

                    $nilaiObj = $nilaiMap->get($detail->kelas_id);
                    $huruf = $nilaiObj && $nilaiObj->nilai_huruf ? strtoupper(trim($nilaiObj->nilai_huruf)) : '';
                    $bobot = ($huruf !== '' && $huruf !== '-' && $huruf !== 'T') ? $this->getBobot($huruf) : 0.0;
                    $isGraded = ($huruf !== '' && $huruf !== '-' && $huruf !== 'T');

                    if (!isset($groupedByMk[$mk->id])) {
                        $groupedByMk[$mk->id] = [
                            'sks' => (int) $mk->sks,
                            'huruf' => $huruf,
                            'bobot' => $bobot,
                            'isGraded' => $isGraded,
                        ];
                    } else {
                        // If repeated, keep the best grade
                        if ($bobot > $groupedByMk[$mk->id]['bobot']) {
                            $groupedByMk[$mk->id]['huruf'] = $huruf;
                            $groupedByMk[$mk->id]['bobot'] = $bobot;
                            $groupedByMk[$mk->id]['isGraded'] = $isGraded;
                        }
                    }
                }
            }

            $totalSks = 0;
            $totalSksLulus = 0;
            $totalBobot = 0;

            foreach ($groupedByMk as $mkData) {
                $totalSks += $mkData['sks'];
                $totalBobot += ($mkData['sks'] * $mkData['bobot']);

                if ($mkData['isGraded'] && in_array($mkData['huruf'], ['A', 'B+', 'B', 'C+', 'C', 'D'])) {
                    $totalSksLulus += $mkData['sks'];
                }
            }

            $ipk = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0.00;

            return [
                'ipk' => $ipk,
                'ips' => $ipk,
                'total_sks' => $totalSks,
                'total_sks_lulus' => $totalSksLulus,
                'total_bobot' => $totalBobot,
            ];
        }

        $nilaiList = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->with('kelas.mataKuliah')
            ->get();

        // If a student repeated a subject, keep the best grade for IPK calculation
        $groupedByMk = [];
        foreach ($nilaiList as $nilai) {
            $mkId = $nilai->kelas?->mata_kuliah_id;
            if (!$mkId) continue;
            
            $bobot = $this->getBobot($nilai->nilai_huruf ?? '');
            if (!isset($groupedByMk[$mkId]) || $bobot > $groupedByMk[$mkId]['bobot']) {
                $groupedByMk[$mkId] = [
                    'nilai' => $nilai,
                    'bobot' => $bobot,
                ];
            }
        }

        $filteredNilai = collect(array_column($groupedByMk, 'nilai'));
        return $this->calculateIndexFromNilai($filteredNilai);
    }

    /**
     * Get grade distribution for a mahasiswa
     */
    public function getGradeDistribution(Mahasiswa $mahasiswa): array
    {
        $nilai = Nilai::where('mahasiswa_id', $mahasiswa->id)->get();
        
        $distribution = [
            'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'T' => 0
        ];

        foreach ($nilai as $n) {
            if (isset($distribution[$n->nilai_huruf])) {
                $distribution[$n->nilai_huruf]++;
            }
        }

        return $distribution;
    }

    /**
     * Get semester-wise IPS history
     */
    public function getIPSHistory(Mahasiswa $mahasiswa): Collection
    {
        $krsHistory = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'approved')
            ->with('tahunAkademik')
            ->orderBy('created_at')
            ->get();

        return $krsHistory->map(function ($krs) use ($mahasiswa) {
            $ipsData = $this->calculateIPS($mahasiswa, $krs->tahun_akademik_id);
            return [
                'tahun_akademik' => $krs->tahunAkademik->tahun . ' ' . $krs->tahunAkademik->semester,
                'tahun_akademik_id' => $krs->tahun_akademik_id,
                'ips' => $ipsData['ips'],
                'total_sks' => $ipsData['total_sks'],
            ];
        });
    }

    /**
     * Determine max SKS allowed based on IPS
     */
    public function getMaxSKS(float $ips): int
    {
        $rules = config('siakad.maks_sks.ips_rules', []);
        
        foreach ($rules as $rule) {
            if ($ips >= $rule['min'] && $ips <= $rule['max']) {
                return $rule['sks'];
            }
        }

        return config('siakad.maks_sks.default', 24);
    }

    /**
     * Get transcript data for mahasiswa (includes all courses in approved KRS)
     */
    public function getTranscript(Mahasiswa $mahasiswa): array
    {
        $krsList = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'approved')
            ->with(['tahunAkademik', 'krsDetail.kelas.mataKuliah'])
            ->orderBy('tahun_akademik_id', 'asc')
            ->get();

        $nilaiMap = Nilai::where('mahasiswa_id', $mahasiswa->id)->get()->keyBy('kelas_id');

        $transcriptSemesters = [];
        $allCourses = collect();

        foreach ($krsList as $krs) {
            $ta = $krs->tahunAkademik;
            $semesterLabel = $ta ? $ta->tahun . ' - ' . ucfirst($ta->semester) : 'Semester ' . $krs->tahun_akademik_id;

            $coursesInSemester = collect();
            $semTotalSks = 0;
            $semTotalBobot = 0;

            foreach ($krs->krsDetail as $detail) {
                $mk = $detail->kelas?->mataKuliah;
                if (!$mk) continue;

                $nilaiObj = $nilaiMap->get($detail->kelas_id);
                $nilaiHuruf = $nilaiObj ? ($nilaiObj->nilai_huruf ?? '-') : '-';
                $nilaiAngka = $nilaiObj ? ($nilaiObj->nilai_angka ?? '-') : '-';
                $bobot = ($nilaiHuruf !== '-' && $nilaiHuruf !== 'T') ? $this->getBobot($nilaiHuruf) : 0;

                $semTotalSks += $mk->sks;
                $semTotalBobot += ($mk->sks * $bobot);

                $courseData = [
                    'kode' => $mk->kode_mk,
                    'nama' => $mk->nama_mk,
                    'sks' => $mk->sks,
                    'nilai_angka' => $nilaiAngka,
                    'nilai_huruf' => $nilaiHuruf,
                    'bobot' => $bobot,
                    'nilai_bobot' => $bobot * $mk->sks,
                    'tahun_akademik' => $semesterLabel,
                ];

                $coursesInSemester->push($courseData);
                $allCourses->push($courseData);
            }

            $ips = $semTotalSks > 0 ? round($semTotalBobot / $semTotalSks, 2) : 0;

            $transcriptSemesters[] = [
                'semester' => $semesterLabel,
                'ips' => $ips,
                'total_sks' => $semTotalSks,
                'courses' => $coursesInSemester,
            ];
        }

        $ipkData = $this->calculateIPK($mahasiswa);
        
        return [
            'mahasiswa' => $mahasiswa,
            'semesters' => $transcriptSemesters,
            'all_courses' => $allCourses,
            'ipk' => $ipkData['ipk'],
            'ips' => $ipkData['ipk'],
            'total_sks' => $ipkData['total_sks'],
            'total_sks_lulus' => $ipkData['total_sks_lulus'],
            'total_bobot' => $ipkData['total_bobot'],
        ];
    }

    /**
     * Calculate index from nilai collection
     */
    private function calculateIndexFromNilai(Collection $nilaiList): array
    {
        $totalSks = 0;
        $totalSksLulus = 0;
        $totalBobot = 0;

        foreach ($nilaiList as $nilai) {
            $mk = $nilai->kelas?->mataKuliah;
            if (!$mk) continue;
            
            $sks = (int) $mk->sks;
            $huruf = $nilai->nilai_huruf ? strtoupper(trim($nilai->nilai_huruf)) : '';
            $bobot = $this->getBobot($huruf);
            
            // Only count if course has a valid letter grade
            if ($huruf !== '' && $huruf !== '-' && $huruf !== 'T') {
                $totalSks += $sks;
                $totalBobot += ($sks * $bobot);

                // Passing grade check (A, B+, B, C+, C, D)
                if (in_array($huruf, ['A', 'B+', 'B', 'C+', 'C', 'D'])) {
                    $totalSksLulus += $sks;
                }
            }
        }

        $index = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0.00;

        return [
            'ipk' => $index,
            'ips' => $index,
            'total_sks' => $totalSks,
            'total_sks_lulus' => $totalSksLulus,
            'total_bobot' => $totalBobot,
        ];
    }

    public function getBobot(string $nilaiHuruf): float
    {
        $konversi = config('siakad.nilai_konversi', []);
        
        foreach ($konversi as $k) {
            if ($k['huruf'] === strtoupper($nilaiHuruf)) {
                return (float)$k['bobot'];
            }
        }

        $fallbackMap = ['A' => 4.0, 'B' => 3.0, 'C' => 2.0, 'D' => 1.0, 'E' => 0.0, 'T' => 0.0];
        return $fallbackMap[strtoupper($nilaiHuruf)] ?? 0.0;
    }
}
