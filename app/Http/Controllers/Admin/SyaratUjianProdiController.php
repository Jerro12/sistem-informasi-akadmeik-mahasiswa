<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SyaratUjianProdi;
use App\Models\Prodi;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SyaratUjianProdiController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SyaratUjianProdi::with('prodi');

        if ($user->role === 'admin_prodi' && $user->prodi_id) {
            $query->where('prodi_id', $user->prodi_id);
        } elseif ($user->role === 'admin_fakultas' && $user->fakultas_id) {
            $query->whereHas('prodi', fn($q) => $q->where('fakultas_id', $user->fakultas_id));
        }

        $syaratList = $query->orderBy('prodi_id')->orderBy('jenis_ujian')->get();

        $prodiList = collect();
        if ($user->role === 'superadmin') {
            $prodiList = Prodi::orderBy('nama')->get();
        } elseif ($user->role === 'admin_fakultas' && $user->fakultas_id) {
            $prodiList = Prodi::where('fakultas_id', $user->fakultas_id)->orderBy('nama')->get();
        }

        return view('admin.syarat-ujian.index', compact('syaratList', 'prodiList'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $rules = [
            'jenis_ujian' => 'required|in:proposal,hasil,sidang',
            'nama_persyaratan' => 'required|string|max:255',
        ];

        if ($user->role === 'superadmin' && !$user->prodi_id) {
            $rules['prodi_id'] = 'required|exists:prodi,id';
        }

        $validated = $request->validate($rules);

        if (!isset($validated['prodi_id'])) {
            $validated['prodi_id'] = $user->prodi_id;
        }

        if (empty($validated['prodi_id'])) {
            return redirect()->back()->with('error', 'Program studi tidak ditemukan untuk akun ini.');
        }

        $validated['file_name_key'] = Str::slug($validated['nama_persyaratan'], '_');
        $validated['is_required'] = $request->boolean('is_required');

        SyaratUjianProdi::create($validated);

        return redirect()->back()->with('success', 'Persyaratan dokumen ujian berhasil ditambahkan.');
    }

    public function destroy(SyaratUjianProdi $syaratUjian)
    {
        $user = auth()->user();

        if ($user->role === 'admin_prodi' && $syaratUjian->prodi_id !== $user->prodi_id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus persyaratan prodi lain.');
        }

        $syaratUjian->delete();

        return redirect()->back()->with('success', 'Persyaratan dokumen ujian berhasil dihapus.');
    }
}
