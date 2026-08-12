<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Pertemuan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MateriController extends Controller
{
    /**
     * Show materi for a specific kelas
     */
    public function index($kelasId)
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Anda tidak memiliki akses sebagai dosen.');
        }
        
        $kelas = $dosen->kelas()
            ->with(['mataKuliah', 'jadwal.pertemuan.materiList'])
            ->findOrFail($kelasId);

        // Get all pertemuan for this kelas
        $pertemuanList = Pertemuan::whereHas('jadwalKuliah', fn($q) => $q->where('kelas_id', $kelasId))
            ->with('materiList')
            ->orderBy('pertemuan_ke')
            ->get();

        return view('dosen.materi.index', compact('kelas', 'pertemuanList'));
    }

    /**
     * Store new materi
     */
    public function store(Request $request, $kelasId)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        $validated = $request->validate([
            'pertemuan_id' => 'required|exists:pertemuan,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|max:20480', // 20MB max
            'link_external' => 'nullable|url|max:500',
        ]);

        try {
            // Verify pertemuan belongs to this kelas
            $pertemuan = Pertemuan::where('id', $validated['pertemuan_id'])
                ->whereHas('jadwalKuliah', fn($q) => $q->where('kelas_id', $kelasId))
                ->firstOrFail();

            $materi = new Materi([
                'pertemuan_id' => $pertemuan->id,
                'judul' => $validated['judul'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'link_external' => $validated['link_external'] ?? null,
                'urutan' => $pertemuan->materiList()->count() + 1,
            ]);

            // Handle file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs("materi/kelas_{$kelasId}", $filename, 'public');

                $materi->file_path = $path;
                $materi->file_name = $file->getClientOriginalName();
                $materi->file_size = $file->getSize();
                $materi->file_type = $file->getMimeType();
            }

            $materi->save();

            return back()->with('success', 'Materi berhasil ditambahkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan saat mengupload materi: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Update materi
     */
    public function update(Request $request, $kelasId, Materi $materi)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        // Verify materi belongs to this kelas
        $pertemuan = $materi->pertemuan;
        if ($pertemuan->jadwalKuliah->kelas_id != $kelasId) {
            abort(403);
        }

        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'file' => 'nullable|file|max:20480',
            'link_external' => 'nullable|url|max:500',
        ]);

        $materi->judul = $validated['judul'];
        $materi->deskripsi = $validated['deskripsi'] ?? null;
        $materi->link_external = $validated['link_external'] ?? null;

        // Handle new file upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($materi->file_path) {
                Storage::disk('public')->delete($materi->file_path);
            }

            $file = $request->file('file');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs("materi/kelas_{$kelasId}", $filename, 'public');

            $materi->file_path = $path;
            $materi->file_name = $file->getClientOriginalName();
            $materi->file_size = $file->getSize();
            $materi->file_type = $file->getMimeType();
        }

        $materi->save();

        return back()->with('success', 'Materi berhasil diupdate.');
    }

    /**
     * Delete materi
     */
    public function destroy($kelasId, Materi $materi)
    {
        $dosen = Auth::user()->dosen;
        $kelas = $dosen->kelas()->findOrFail($kelasId);

        // Verify materi belongs to this kelas
        $pertemuan = $materi->pertemuan;
        if ($pertemuan->jadwalKuliah->kelas_id != $kelasId) {
            abort(403);
        }

        // Delete file if exists
        if ($materi->file_path) {
            Storage::disk('public')->delete($materi->file_path);
        }

        $materi->delete();

        return back()->with('success', 'Materi berhasil dihapus.');
    }

    /**
     * Download materi file
     */
    public function download($kelasId, Materi $materi)
    {
        $dosen = Auth::user()->dosen;
        if (!$dosen) {
            abort(403, 'Akses ditolak.');
        }

        $kelas = Kelas::where(function ($q) use ($dosen) {
            $q->where('dosen_id', $dosen->id)
              ->orWhereHas('jadwal', fn($j) => $j->where('dosen_id', $dosen->id));
        })->findOrFail($kelasId);

        // Verify materi belongs to this kelas
        if ($materi->pertemuan?->jadwalKuliah?->kelas_id != $kelasId) {
            $pertemuanBelongs = Pertemuan::where('id', $materi->pertemuan_id)
                ->whereHas('jadwalKuliah', fn($q) => $q->where('kelas_id', $kelasId))
                ->exists();
            if (!$pertemuanBelongs && $materi->pertemuan?->kelas_id != $kelasId) {
                abort(403, 'Akses ditolak.');
            }
        }

        return $this->downloadFile($materi->file_path, $materi->file_name);
    }

    private function downloadFile($filePath, $fileName = null)
    {
        if (!$filePath) {
            return back()->with('error', 'File tidak ditemukan atau belum diunggah.');
        }

        $cleanPath = ltrim($filePath, '/');
        $cleanPath = preg_replace('/^(public\/|storage\/)+/', '', $cleanPath);

        $pathsToTry = [
            $filePath,
            $cleanPath,
            'public/' . $cleanPath,
            ltrim($filePath, '/'),
        ];

        foreach ($pathsToTry as $path) {
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->download($path, $fileName);
            }
            if (Storage::disk('local')->exists($path)) {
                return Storage::disk('local')->download($path, $fileName);
            }
        }

        $absolutePaths = [
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            storage_path('app/' . ltrim($filePath, '/')),
        ];

        foreach ($absolutePaths as $absPath) {
            if (file_exists($absPath) && is_file($absPath)) {
                return response()->download($absPath, $fileName);
            }
        }

        return back()->with('error', 'File tidak ditemukan di server.');
    }
}
