<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kurikulum;
use App\Models\Prodi;
use Illuminate\Http\Request;

class KurikulumController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $query = Kurikulum::with('prodi');
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
            $query->where('nama', 'like', "%{$search}%");
        }

        $kurikulum = $query->paginate(config('siakad.pagination', 15));
        
        $prodiQuery = Prodi::query();
        if (!$isSuperAdmin) {
            if ($user->role === 'admin_prodi' && $user->prodi_id) {
                $prodiQuery->where('id', $user->prodi_id);
            } elseif ($user->fakultas_id) {
                $prodiQuery->where('fakultas_id', $user->fakultas_id);
            }
        }
        $prodis = $prodiQuery->get();

        return view('admin.kurikulum.index', compact('kurikulum', 'prodis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama' => 'required|string|max:255',
            'tahun_mulai' => 'required|numeric',
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

        Kurikulum::create([
            'prodi_id' => $request->prodi_id,
            'nama' => $request->nama,
            'tahun_mulai' => $request->tahun_mulai,
            'is_active' => $request->has('is_active') ? $request->is_active : true,
        ]);

        return redirect()->back()->with('success', 'Kurikulum berhasil ditambahkan');
    }

    public function update(Request $request, Kurikulum $kurikulum)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'nama' => 'required|string|max:255',
            'tahun_mulai' => 'required|numeric',
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

        $kurikulum->update([
            'prodi_id' => $request->prodi_id,
            'nama' => $request->nama,
            'tahun_mulai' => $request->tahun_mulai,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->back()->with('success', 'Kurikulum berhasil diperbarui');
    }

    public function destroy(Kurikulum $kurikulum)
    {
        // Security check
        $user = auth()->user();
        if (!$user->isSuperAdmin()) {
            if ($user->role === 'admin_prodi') {
                if ($kurikulum->prodi_id !== $user->prodi_id) abort(403, 'Unauthorized action.');
            } else {
                if ($kurikulum->prodi->fakultas_id !== $user->fakultas_id) {
                    abort(403, 'Unauthorized action.');
                }
            }
        }

        if ($kurikulum->mahasiswa()->count() > 0 || $kurikulum->mataKuliah()->count() > 0) {
            return redirect()->back()->with('error', 'Kurikulum tidak dapat dihapus karena masih digunakan.');
        }

        $kurikulum->delete();
        return redirect()->back()->with('success', 'Kurikulum berhasil dihapus');
    }
}
