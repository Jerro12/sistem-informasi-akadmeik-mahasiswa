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
        $query = Konsentrasi::with('prodi');

        // Admin fakultas melihat se-fakultas, admin prodi hanya prodi miliknya
        if (!$isSuperAdmin) {
            if ($user->role === 'admin_prodi' && $user->prodi_id) {
                $query->where('prodi_id', $user->prodi_id);
            } elseif ($user->fakultas_id) {
                $query->whereHas('prodi', function ($q) use ($user) {
                    $q->where('fakultas_id', $user->fakultas_id);
                });
            }
        }

        if ($search = $request->get('search')) {
            $query->where('nama_konsentrasi', 'like', "%{$search}%");
        }

        $konsentrasi = $query->paginate(config('siakad.pagination', 15));
        
        $prodiQuery = Prodi::query();
        if (!$isSuperAdmin) {
            if ($user->role === 'admin_prodi' && $user->prodi_id) {
                $prodiQuery->where('id', $user->prodi_id);
            } elseif ($user->fakultas_id) {
                $prodiQuery->where('fakultas_id', $user->fakultas_id);
            }
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

        // Security check: ensure admin prodi can only add to their own prodi
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            if ($user->role === 'admin_prodi') {
                if ($request->prodi_id != $user->prodi_id) abort(403, 'Unauthorized action.');
            } else {
                $prodi = Prodi::findOrFail($request->prodi_id);
                if ($prodi->fakultas_id !== $user->fakultas_id) {
                    abort(403, 'Unauthorized action.');
                }
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
            if ($user->role === 'admin_prodi') {
                if ($request->prodi_id != $user->prodi_id) abort(403, 'Unauthorized action.');
            } else {
                $prodi = Prodi::findOrFail($request->prodi_id);
                if ($prodi->fakultas_id !== $user->fakultas_id) {
                    abort(403, 'Unauthorized action.');
                }
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
            if ($user->role === 'admin_prodi') {
                if ($konsentrasi->prodi_id !== $user->prodi_id) abort(403, 'Unauthorized action.');
            } else {
                if ($konsentrasi->prodi->fakultas_id !== $user->fakultas_id) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        if ($konsentrasi->krs()->count() > 0 || $konsentrasi->mataKuliah()->count() > 0) {
            return redirect()->back()->with('error', 'Konsentrasi tidak dapat dihapus karena masih digunakan.');
        }

        $konsentrasi->delete();
        return redirect()->back()->with('success', 'Konsentrasi berhasil dihapus');
    }
}
