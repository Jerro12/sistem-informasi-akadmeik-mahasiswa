<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BiayaKuliah;
use App\Models\TahunAkademik;
use App\Models\Prodi;
use Illuminate\Http\Request;

class BiayaKuliahController extends Controller
{
    public function index()
    {
        $tahunAkademik = TahunAkademik::orderBy('tahun', 'desc')->get();
        $prodi = Prodi::with('fakultas')->get();
        $biaya = BiayaKuliah::with(['tahunAkademik', 'prodi.fakultas'])
            ->orderBy('tahun_akademik_id', 'desc')
            ->paginate(config('siakad.pagination', 20));

        return view('admin.biaya-kuliah.index', compact('tahunAkademik', 'prodi', 'biaya'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun_akademik_id' => 'required|exists:tahun_akademik,id',
            'prodi_id' => 'required|exists:prodi,id',
            'nominal' => 'required|numeric|min:0',
        ]);

        BiayaKuliah::updateOrCreate(
            ['tahun_akademik_id' => $validated['tahun_akademik_id'], 'prodi_id' => $validated['prodi_id']],
            ['nominal' => $validated['nominal']]
        );

        return back()->with('success', 'Biaya kuliah berhasil diatur.');
    }

    public function destroy(BiayaKuliah $biayaKuliah)
    {
        $biayaKuliah->delete();
        return back()->with('success', 'Data biaya berhasil dihapus.');
    }
}
