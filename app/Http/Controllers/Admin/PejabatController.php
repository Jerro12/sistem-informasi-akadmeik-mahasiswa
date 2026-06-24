<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prodi;
use App\Models\Fakultas;
use Illuminate\Http\Request;

class PejabatController extends Controller
{
    public function index()
    {
        $prodis = Prodi::with('fakultas')->orderBy('nama')->get();
        $fakultasList = Fakultas::orderBy('nama')->get();

        return view('admin.pejabat.index', compact('prodis', 'fakultasList'));
    }

    public function updateProdi(Request $request, Prodi $prodi)
    {
        $validated = $request->validate([
            'nama_ketua_prodi' => 'nullable|string|max:255',
            'nidn_ketua_prodi' => 'nullable|string|max:50',
        ]);

        $prodi->update($validated);

        return redirect()->back()->with('success', "Data pejabat Prodi {$prodi->nama} berhasil disimpan.");
    }

    public function updateFakultas(Request $request, Fakultas $fakultas)
    {
        $validated = $request->validate([
            'nama_dekan' => 'nullable|string|max:255',
            'nama_wakil_dekan1' => 'nullable|string|max:255',
        ]);

        $fakultas->update($validated);

        return redirect()->back()->with('success', "Data pejabat Fakultas {$fakultas->nama} berhasil disimpan.");
    }
}
