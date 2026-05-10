<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Konsentrasi;
use App\Models\Prodi;
use Illuminate\Http\Request;

class KonsentrasiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $fakultasId = $user->fakultas_id;

        $query = Konsentrasi::with('prodi');

        // Admin fakultas hanya bisa melihat konsentrasi di prodinya
        if (!$isSuperAdmin && $fakultasId) {
            $query->whereHas('prodi', function ($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            });
        }

        if ($search = $request->get('search')) {
            $query->where('nama_konsentrasi', 'like', "%{$search}%");
        }

        $konsentrasi = $query->paginate(config('siakad.pagination', 15));
        
        $prodiQuery = Prodi::query();
        if (!$isSuperAdmin && $fakultasId) {
            $prodiQuery->where('fakultas_id', $fakultasId);
        }
        $prodis = $prodiQuery->get();

        return view('admin.konsentrasi.index', compact('konsentrasi', 'prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama_konsentrasi' => 'required|string|max:255',
            'kode_konsentrasi' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        // Security check: ensure admin fakultas can only add to their own prodi
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $prodi = Prodi::findOrFail($request->prodi_id);
            if ($prodi->fakultas_id !== $user->fakultas_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        Konsentrasi::create([
            'prodi_id' => $request->prodi_id,
            'nama_konsentrasi' => $request->nama_konsentrasi,
            'kode_konsentrasi' => $request->kode_konsentrasi,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->back()->with('success', 'Konsentrasi berhasil ditambahkan');
    }

    public function update(Request $request, Konsentrasi $konsentrasi)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama_konsentrasi' => 'required|string|max:255',
            'kode_konsentrasi' => 'nullable|string|max:50',
            'is_active' => 'boolean'
        ]);

        // Security check
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            $prodi = Prodi::findOrFail($request->prodi_id);
            if ($prodi->fakultas_id !== $user->fakultas_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        $konsentrasi->update([
            'prodi_id' => $request->prodi_id,
            'nama_konsentrasi' => $request->nama_konsentrasi,
            'kode_konsentrasi' => $request->kode_konsentrasi,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'Konsentrasi berhasil diperbarui');
    }

    public function destroy(Konsentrasi $konsentrasi)
    {
        // Security check
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            if ($konsentrasi->prodi->fakultas_id !== $user->fakultas_id) {
                abort(403, 'Unauthorized action.');
            }
        }

        if ($konsentrasi->krs()->count() > 0 || $konsentrasi->mataKuliah()->count() > 0) {
            return redirect()->back()->with('error', 'Konsentrasi tidak dapat dihapus karena masih digunakan.');
        }

        $konsentrasi->delete();
        return redirect()->back()->with('success', 'Konsentrasi berhasil dihapus');
    }
}
