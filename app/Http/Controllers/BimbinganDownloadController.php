<?php

namespace App\Http\Controllers;

use App\Models\BimbinganSkripsi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class BimbinganDownloadController extends Controller
{
    public function download(BimbinganSkripsi $bimbingan)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401, 'Unauthenticated');
        }

        $allowed = false;

        if ($user->isSuperAdmin()) {
            $allowed = true;
        } elseif ($user->role === 'admin_fakultas') {
            if ($bimbingan->skripsi && $bimbingan->skripsi->mahasiswa && $bimbingan->skripsi->mahasiswa->prodi) {
                if ($user->fakultas_id === $bimbingan->skripsi->mahasiswa->prodi->fakultas_id) {
                    $allowed = true;
                }
            }
        } elseif ($user->role === 'admin_prodi') {
            if ($bimbingan->skripsi && $bimbingan->skripsi->mahasiswa) {
                if ($user->prodi_id === $bimbingan->skripsi->mahasiswa->prodi_id) {
                    $allowed = true;
                }
            }
        } elseif ($user->role === 'dosen') {
            if ($bimbingan->skripsi && $user->dosen) {
                if ($user->dosen->id === $bimbingan->skripsi->pembimbing1_id || $user->dosen->id === $bimbingan->skripsi->pembimbing2_id) {
                    $allowed = true;
                }
            }
        } elseif ($user->role === 'mahasiswa') {
            if ($bimbingan->skripsi && $user->mahasiswa) {
                if ($user->mahasiswa->id === $bimbingan->skripsi->mahasiswa_id) {
                    $allowed = true;
                }
            }
        }

        if (!$allowed) {
            abort(403, 'Akses ditolak.');
        }

        if (!$bimbingan->file_dokumen) {
            abort(404, 'File tidak ditemukan.');
        }

        // Clean up leading slash/storage reference if any
        $filePath = ltrim($bimbingan->file_dokumen, '/');
        $filePath = preg_replace('/^storage\//', '', $filePath);

        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File dokumen tidak ditemukan di storage: ' . $filePath);
        }

        return Storage::disk('public')->download($filePath);
    }
}
