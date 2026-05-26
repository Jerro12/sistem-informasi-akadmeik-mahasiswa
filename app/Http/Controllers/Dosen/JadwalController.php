<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $dosen = $user->dosen;

        if (!$dosen) {
            abort(403, 'Unauthorized');
        }

        $tahunAkademik = TahunAkademik::orderBy('tahun', 'desc')->get();
        
        $selectedTaId = $request->tahun_akademik_id ?? TahunAkademik::active()?->id;

        $kelasList = Kelas::where('dosen_id', $dosen->id)
            ->where('tahun_akademik_id', $selectedTaId)
            ->with(['mataKuliah', 'jadwal'])
            ->get();

        $hariOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $jadwalPerHari = collect();

        foreach ($hariOrder as $hari) {
            $jadwalHariIni = collect();

            foreach ($kelasList as $kelas) {
                foreach ($kelas->jadwal as $jadwal) {
                    if ($jadwal->hari === $hari) {
                        $jadwalHariIni->push([
                            'kelas' => $kelas,
                            'jadwal' => $jadwal,
                        ]);
                    }
                }
            }

            if ($jadwalHariIni->isNotEmpty()) {
                $jadwalHariIni = $jadwalHariIni->sortBy(fn($j) => $j['jadwal']->jam_mulai);
                $jadwalPerHari[$hari] = $jadwalHariIni;
            }
        }

        return view('dosen.jadwal.index', compact('jadwalPerHari', 'tahunAkademik', 'selectedTaId'));
    }
}
