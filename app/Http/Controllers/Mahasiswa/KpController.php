<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\KerjaPraktek;
use App\Models\LogbookKp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KpController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $kp = KerjaPraktek::where('mahasiswa_id', $mahasiswa->id)
            ->with(['pembimbing.user', 'logbook'])
            ->first();

        $logbookList = $kp ? $kp->logbook()->get() : collect();

        return view('mahasiswa.kp.index', compact('mahasiswa', 'kp', 'logbookList'));
    }

    public function create()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $existing = KerjaPraktek::where('mahasiswa_id', $mahasiswa->id)->first();
        
        if ($existing) {
            return redirect()->route('mahasiswa.kp.index')->with('error', 'Anda sudah memiliki pengajuan KP');
        }

        return view('mahasiswa.kp.create', compact('mahasiswa'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $validated = $request->validate([
            'nama_perusahaan' => 'required|string|max:255',
            'alamat_perusahaan' => 'nullable|string',
            'bidang_usaha' => 'nullable|string|max:100',
            'nama_pembimbing_lapangan' => 'nullable|string|max:100',
            'jabatan_pembimbing_lapangan' => 'nullable|string|max:100',
            'no_hp_mahasiswa' => 'required|string|max:20',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
        ]);

        KerjaPraktek::create([
            'mahasiswa_id' => $mahasiswa->id,
            ...$validated,
            'status' => KerjaPraktek::STATUS_PENGAJUAN,
        ]);

        return redirect()->route('mahasiswa.kp.index')->with('success', 'Pengajuan KP berhasil dikirim');
    }

    public function storeLogbook(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $kp = KerjaPraktek::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

        $validated = $request->validate([
            'tanggal' => 'required|date',
            'jam_masuk' => 'nullable|date_format:H:i',
            'jam_keluar' => 'nullable|date_format:H:i',
            'kegiatan' => 'required|string',
        ]);

        LogbookKp::create([
            'kerja_praktek_id' => $kp->id,
            ...$validated,
            'status' => LogbookKp::STATUS_PENDING,
        ]);

        return redirect()->route('mahasiswa.kp.index')->with('success', 'Logbook berhasil ditambahkan');
    }

    public function cetakSurat()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $kp = KerjaPraktek::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotIn('status', [KerjaPraktek::STATUS_PENGAJUAN, KerjaPraktek::STATUS_DITOLAK])
            ->firstOrFail();

        // Get Ketua Prodi
        $mahasiswa->load('prodi');
        $ketuaProdi = $mahasiswa->prodi->nama_ketua_prodi ?? '.......................';
        $nidnKetuaProdi = $mahasiswa->prodi->nidn_ketua_prodi ?? '.......................';

        // Fetch other students who are also approved in the same company and same dates (group application)
        $groupMembers = KerjaPraktek::where('nama_perusahaan', $kp->nama_perusahaan)
            ->where('tanggal_mulai', $kp->tanggal_mulai)
            ->where('tanggal_selesai', $kp->tanggal_selesai)
            ->whereNotIn('status', [KerjaPraktek::STATUS_PENGAJUAN, KerjaPraktek::STATUS_DITOLAK])
            ->with('mahasiswa.user')
            ->get();

        return view('mahasiswa.kp.surat', compact('mahasiswa', 'kp', 'ketuaProdi', 'nidnKetuaProdi', 'groupMembers'));
    }
}
