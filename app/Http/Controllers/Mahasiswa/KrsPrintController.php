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

        return view('mahasiswa.krs.print', compact('krs', 'mahasiswa'));
    }
}
