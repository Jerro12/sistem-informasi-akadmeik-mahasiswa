<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use Illuminate\Support\Facades\Auth;

class KrsPrintController extends Controller
{
    public function print()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->latest()
            ->with(['tahunAkademik', 'krsDetail.kelas.mataKuliah', 'krsDetail.kelas.dosen.user', 'mahasiswa.prodi.fakultas', 'mahasiswa.dosenPa.user'])
            ->first();

        if (!$krs) {
            return redirect()->back()->with('error', 'Data KRS tidak ditemukan. Silakan lakukan pengisian KRS terlebih dahulu.');
        }

        if (!$krs->tahunAkademik) {
            return redirect()->back()->with('error', 'Tahun Akademik pada KRS ini tidak valid atau telah dihapus.');
        }

        // Get array of taken mata kuliah IDs and kelas IDs
        $takenMkIds = $krs->krsDetail->pluck('kelas.mata_kuliah_id')->filter()->toArray();
        $takenKelasIds = $krs->krsDetail->pluck('kelas_id')->filter()->toArray();

        // Ambil semua mata kuliah untuk prodi mahasiswa pada semester ganjil/genap sesuai tahun akademik KRS
        $semesterType = strtolower($krs->tahunAkademik->semester);
        $allMataKuliah = \App\Models\MataKuliah::where(function($q) use ($mahasiswa, $semesterType, $krs, $takenMkIds) {
                $q->where(function($q2) use ($mahasiswa, $semesterType, $krs) {
                    $q2->where('prodi_id', $mahasiswa->prodi_id)
                        ->where(function($q3) use ($semesterType) {
                            if ($semesterType === 'ganjil') {
                                $q3->whereRaw('semester % 2 != 0');
                            } else {
                                $q3->whereRaw('semester % 2 = 0');
                            }
                        })
                        ->when($mahasiswa->kurikulum_id, function($q3) use ($mahasiswa) {
                            $q3->where(function($query) use ($mahasiswa) {
                                $query->where('kurikulum_id', $mahasiswa->kurikulum_id)
                                      ->orWhereNull('kurikulum_id');
                            });
                        })
                        ->when($krs->konsentrasi_id, function($q3) use ($krs) {
                            $q3->where(function($query) use ($krs) {
                                $query->where('konsentrasi_id', $krs->konsentrasi_id)
                                      ->orWhereNull('konsentrasi_id');
                            });
                        }, function($q3) {
                            $q3->whereNull('konsentrasi_id');
                        });
                })
                ->orWhereIn('id', $takenMkIds);
            })
            ->with(['kelas' => function($q) use ($krs) {
                $q->where('tahun_akademik_id', $krs->tahun_akademik_id)->with('dosen.user');
            }])
            ->get();

        // Grouping berdasarkan semester mata kuliah
        $groupedMataKuliah = $allMataKuliah->groupBy('semester')->sortKeys();

        return view('mahasiswa.krs.print', compact('krs', 'mahasiswa', 'groupedMataKuliah', 'takenMkIds', 'takenKelasIds'));
    }
}
