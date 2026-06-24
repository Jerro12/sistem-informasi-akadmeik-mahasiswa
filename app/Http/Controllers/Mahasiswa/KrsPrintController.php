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
            ->firstOrFail();

        // Ambil semua kelas yang ditawarkan pada tahun akademik yang sama untuk prodi mahasiswa
        $allKelas = \App\Models\Kelas::where('tahun_akademik_id', $krs->tahun_akademik_id)
            ->whereHas('mataKuliah', function($q) use ($mahasiswa) {
                $q->where('prodi_id', $mahasiswa->prodi_id);
            })
            ->with(['mataKuliah', 'dosen.user'])
            ->get();

        // Grouping berdasarkan semester mata kuliah
        $groupedKelas = $allKelas->groupBy(function($item) {
            return $item->mataKuliah->semester;
        })->sortKeys();

        // Get array of taken kelas IDs
        $takenKelasIds = $krs->krsDetail->pluck('kelas_id')->toArray();

        return view('mahasiswa.krs.print', compact('krs', 'mahasiswa', 'groupedKelas', 'takenKelasIds'));
    }
}
