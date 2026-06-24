<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Skripsi;
use App\Models\BimbinganSkripsi;
use App\Models\Dosen;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkripsiController extends Controller
{
    public function index()
    {
        $dosen = Auth::user()->dosen;
        
        if (!$dosen) {
            abort(403);
        }

        // Get all skripsi where this dosen is pembimbing
        $skripsiList = Skripsi::where('pembimbing1_id', $dosen->id)
            ->orWhere('pembimbing2_id', $dosen->id)
            ->with(['mahasiswa.user', 'pembimbing1.user', 'pembimbing2.user'])
            ->orderByRaw("CASE status 
                WHEN 'bimbingan' THEN 1 
                WHEN 'pengajuan' THEN 2 
                WHEN 'review' THEN 3 
                WHEN 'seminar_proposal' THEN 4 
                WHEN 'penelitian' THEN 5 
                WHEN 'seminar_hasil' THEN 6 
                WHEN 'sidang' THEN 7 
                WHEN 'revisi' THEN 8 
                WHEN 'selesai' THEN 9 
                WHEN 'ditolak' THEN 10 
                WHEN 'diterima' THEN 11 
                ELSE 12 
            END ASC")
            ->get();

        // Pending bimbingan
        $pendingBimbingan = BimbinganSkripsi::where('dosen_id', $dosen->id)
            ->where('status', BimbinganSkripsi::STATUS_MENUNGGU)
            ->with('skripsi.mahasiswa.user')
            ->count();

        return view('dosen.skripsi.index', compact('dosen', 'skripsiList', 'pendingBimbingan'));
    }

    public function show(Skripsi $skripsi)
    {
        $dosen = Auth::user()->dosen;

        // Verify dosen is pembimbing
        if ($skripsi->pembimbing1_id !== $dosen->id && $skripsi->pembimbing2_id !== $dosen->id) {
            abort(403, 'Anda bukan pembimbing skripsi ini');
        }

        $skripsi->load(['mahasiswa.user', 'pembimbing1.user', 'pembimbing2.user', 'bimbingan.dosen.user']);

        return view('dosen.skripsi.show', compact('dosen', 'skripsi'));
    }

    public function reviewBimbingan(Request $request, BimbinganSkripsi $bimbingan)
    {
        $dosen = Auth::user()->dosen;

        if (!$bimbingan->skripsi || ($bimbingan->skripsi->pembimbing1_id !== $dosen->id && $bimbingan->skripsi->pembimbing2_id !== $dosen->id)) {
            abort(403);
        }

        $validated = $request->validate([
            'catatan_dosen' => 'required|string',
            'status' => 'required|in:disetujui,revisi',
        ]);

        $bimbingan->update([
            'catatan_dosen' => $validated['catatan_dosen'],
            'status' => $validated['status'],
            'dosen_id' => $dosen->id,
        ]);

        return redirect()->back()->with('success', 'Review bimbingan berhasil disimpan');
    }

    public function updateStatus(Request $request, Skripsi $skripsi)
    {
        $dosen = Auth::user()->dosen;

        if ($skripsi->pembimbing1_id !== $dosen->id && $skripsi->pembimbing2_id !== $dosen->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Skripsi::getStatusList())),
        ]);

        $skripsi->update(['status' => $validated['status']]);

        return redirect()->back()->with('success', 'Status skripsi berhasil diupdate');
    }

    /**
     * Dosen menyetujui penugasan sebagai pembimbing skripsi
     */
    public function approve(Skripsi $skripsi)
    {
        $dosen = Auth::user()->dosen;

        if ($skripsi->pembimbing1_id !== $dosen->id && $skripsi->pembimbing2_id !== $dosen->id) {
            abort(403, 'Anda bukan pembimbing skripsi ini');
        }

        // Tentukan field berdasarkan pembimbing 1 atau 2
        if ($skripsi->pembimbing1_id === $dosen->id) {
            $skripsi->update(['pembimbing1_approved' => true, 'pembimbing1_catatan' => null]);
        } else {
            $skripsi->update(['pembimbing2_approved' => true, 'pembimbing2_catatan' => null]);
        }

        return redirect()->back()->with('success', 'Anda telah menyetujui penugasan sebagai pembimbing skripsi ini.');
    }

    /**
     * Dosen menolak penugasan sebagai pembimbing skripsi (wajib isi komentar)
     */
    public function reject(Request $request, Skripsi $skripsi)
    {
        $dosen = Auth::user()->dosen;

        if ($skripsi->pembimbing1_id !== $dosen->id && $skripsi->pembimbing2_id !== $dosen->id) {
            abort(403, 'Anda bukan pembimbing skripsi ini');
        }

        $validated = $request->validate([
            'catatan' => 'required|string|min:5',
        ], [
            'catatan.required' => 'Alasan penolakan wajib diisi.',
            'catatan.min' => 'Alasan penolakan minimal 5 karakter.',
        ]);

        if ($skripsi->pembimbing1_id === $dosen->id) {
            $skripsi->update([
                'pembimbing1_approved' => false,
                'pembimbing1_catatan' => $validated['catatan'],
            ]);
        } else {
            $skripsi->update([
                'pembimbing2_approved' => false,
                'pembimbing2_catatan' => $validated['catatan'],
            ]);
        }

        return redirect()->back()->with('success', 'Anda telah menolak penugasan sebagai pembimbing. Admin akan diberitahu.');
    }
}
