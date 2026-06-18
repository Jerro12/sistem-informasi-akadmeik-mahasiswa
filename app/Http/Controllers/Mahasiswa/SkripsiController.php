<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Skripsi;
use App\Models\BimbinganSkripsi;
use App\Models\PendaftaranUjian;
use App\Models\SyaratUjianUpload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SkripsiController extends Controller
{
    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        if (!$mahasiswa) {
            abort(403);
        }

        $skripsi = Skripsi::where('mahasiswa_id', $mahasiswa->id)
            ->with(['pembimbing1.user', 'pembimbing2.user', 'bimbingan'])
            ->first();

        $bimbinganList = $skripsi ? $skripsi->bimbingan()->with('dosen.user')->get() : collect();
        
        $pendaftaranList = $skripsi 
            ? PendaftaranUjian::where('skripsi_id', $skripsi->id)
                ->with(['penguji1.user', 'penguji2.user', 'syaratUpload'])
                ->orderBy('created_at', 'desc')
                ->get()
            : collect();

        $syaratProdi = \App\Models\SyaratUjianProdi::where('prodi_id', $mahasiswa->prodi_id)
            ->orderBy('id')
            ->get()
            ->groupBy('jenis_ujian');

        return view('mahasiswa.skripsi.index', compact('mahasiswa', 'skripsi', 'bimbinganList', 'pendaftaranList', 'syaratProdi'));
    }

    public function create()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        
        // Check if already has skripsi
        $existing = Skripsi::where('mahasiswa_id', $mahasiswa->id)->first();
        if ($existing) {
            return redirect()->route('mahasiswa.skripsi.index')
                ->with('error', 'Anda sudah memiliki pengajuan skripsi');
        }

        return view('mahasiswa.skripsi.create', compact('mahasiswa'));
    }

    public function store(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;

        $validated = $request->validate([
            'judul' => 'required|string|max:500',
            'abstrak' => 'nullable|string',
            'bidang_kajian' => 'nullable|string|max:100',
        ]);

        Skripsi::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul' => $validated['judul'],
            'abstrak' => $validated['abstrak'],
            'bidang_kajian' => $validated['bidang_kajian'],
            'status' => Skripsi::STATUS_PENGAJUAN,
            'tanggal_pengajuan' => now(),
        ]);

        return redirect()->route('mahasiswa.skripsi.index')
            ->with('success', 'Pengajuan judul skripsi berhasil dikirim');
    }

    public function storeBimbingan(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $skripsi = Skripsi::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

        $validated = $request->validate([
            'dosen_id' => 'required|in:' . implode(',', array_filter([$skripsi->pembimbing1_id, $skripsi->pembimbing2_id])),
            'catatan_mahasiswa' => 'required|string',
            'file_dokumen' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        $filePath = null;
        if ($request->hasFile('file_dokumen')) {
            $filePath = $request->file('file_dokumen')->store('skripsi/bimbingan', 'public');
        }

        BimbinganSkripsi::create([
            'skripsi_id' => $skripsi->id,
            'dosen_id' => $validated['dosen_id'],
            'tanggal_bimbingan' => now(),
            'catatan_mahasiswa' => $validated['catatan_mahasiswa'],
            'file_dokumen' => $filePath,
            'status' => BimbinganSkripsi::STATUS_MENUNGGU,
        ]);

        return redirect()->route('mahasiswa.skripsi.index')
            ->with('success', 'Catatan bimbingan berhasil dikirim');
    }

    public function daftarUjian(Request $request)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $skripsi = Skripsi::where('mahasiswa_id', $mahasiswa->id)->firstOrFail();

        $request->validate([
            'jenis_ujian' => 'required|in:proposal,hasil,sidang',
        ]);

        $jenis = $request->jenis_ujian;

        // Verify if they already have a pending or approved registration for this exam type
        $existing = PendaftaranUjian::where('skripsi_id', $skripsi->id)
            ->where('jenis_ujian', $jenis)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existing) {
            return redirect()->back()->with('error', 'Anda sudah mendaftar ujian ' . ucfirst($jenis) . ' atau pendaftaran Anda sedang diproses.');
        }

        // Fetch requirements from SyaratUjianProdi
        $syaratProdi = \App\Models\SyaratUjianProdi::where('prodi_id', $mahasiswa->prodi_id)
            ->where('jenis_ujian', $jenis)
            ->get();

        $requirements = [];
        foreach ($syaratProdi as $syarat) {
            $requirements[$syarat->nama_persyaratan] = [
                'field' => $syarat->file_name_key,
                'required' => $syarat->is_required
            ];
        }

        // Validate that all required files are present and are files
        $rules = [];
        foreach ($requirements as $label => $req) {
            $rule = $req['required'] ? 'required|file|mimes:pdf,doc,docx,zip|max:10240' : 'nullable|file|mimes:pdf,doc,docx,zip|max:10240';
            $rules[$req['field']] = $rule;
        }
        $request->validate($rules);

        // Create Pendaftaran Ujian record
        $pendaftaran = PendaftaranUjian::create([
            'mahasiswa_id' => $mahasiswa->id,
            'skripsi_id' => $skripsi->id,
            'jenis_ujian' => $jenis,
            'status' => 'pending'
        ]);

        // Upload and create Syarat Ujian record
        foreach ($requirements as $label => $req) {
            $fieldName = $req['field'];
            if ($request->hasFile($fieldName)) {
                $path = $request->file($fieldName)->store('skripsi/persyaratan_ujian', 'public');
                SyaratUjianUpload::create([
                    'pendaftaran_ujian_id' => $pendaftaran->id,
                    'nama_persyaratan' => $label,
                    'file_path' => $path,
                    'status' => 'pending'
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pendaftaran ujian ' . ucfirst($jenis) . ' berhasil diajukan.');
    }
}
