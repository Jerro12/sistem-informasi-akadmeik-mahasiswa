<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Services\PenilaianService;
use Illuminate\Http\Request;

class PenilaianController extends Controller
{
    protected $penilaianService;

    public function __construct(PenilaianService $penilaianService)
    {
        $this->penilaianService = $penilaianService;
    }

    /**
     * Tampilkan daftar kelas yang dapat dinilai
     */
    public function index(Request $request)
    {
        $query = Kelas::with(['mataKuliah', 'dosen.user', 'dosen.prodi', 'nilai']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('mataKuliah', fn($q2) => $q2->where('nama_mk', 'like', "%{$search}%")->orWhere('kode_mk', 'like', "%{$search}%"))
                  ->orWhereHas('dosen.user', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
            });
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Filter by prodi
        if ($prodiId = $request->get('prodi_id')) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $prodiId));
        }

        // Filter by tahun_akademik_id
        if ($taId = $request->get('tahun_akademik_id')) {
            $query->where('tahun_akademik_id', $taId);
        }

        $tahunAkademiks = \App\Models\TahunAkademik::orderBy('tahun', 'desc')->orderBy('semester', 'desc')->get();
        $prodis = \App\Models\Prodi::query();
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $prodis->where('fakultas_id', $request->get('fakultas_scope'));
        }
        
        // Scope for admin_prodi
        $user = auth()->user();
        if ($user && $user->role === 'admin_prodi' && $user->prodi_id) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $user->prodi_id));
            $prodis->where('id', $user->prodi_id);
        }
        
        $prodis = $prodis->get();

        $kelas = $query->orderBy('nama_kelas', 'asc')
            ->paginate(config('siakad.pagination', 15))
            ->withQueryString();

        return view('admin.penilaian.index', compact('kelas', 'tahunAkademiks', 'prodis'));
    }

    /**
     * Tampilkan halaman input nilai untuk kelas terpilih
     */
    public function show($id, Request $request)
    {
        $query = Kelas::with(['mataKuliah', 'krsDetail.krs.mahasiswa.user', 'nilai', 'dosen.user']);

        // Scoping check for safety
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        $kelas = $query->findOrFail($id);

        return view('admin.penilaian.show', compact('kelas'));
    }

    /**
     * Simpan nilai mahasiswa dari admin secara massal
     */
    public function store(Request $request, $kelasId)
    {
        if (auth()->user()->role === 'admin_fakultas') {
            return redirect()->back()->with('error', 'Admin Fakultas hanya memiliki akses monitoring nilai (read-only).');
        }

        $request->validate([
            'nilai'   => 'required|array',
            'nilai.*' => 'nullable|numeric|min:0|max:100',
        ]);

        $query = Kelas::query();
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        $kelas = $query->findOrFail($kelasId);

        // nilai[] key adalah krs_detail_id → resolve mahasiswa_id
        $krsDetails = \App\Models\KrsDetail::whereIn('id', array_keys($request->nilai))
            ->with('krs')
            ->get()
            ->keyBy('id');

        $dataNilaiByMahasiswa = [];
        foreach ($request->nilai as $krsDetailId => $nilaiAngka) {
            if (is_null($nilaiAngka)) continue;
            $detail = $krsDetails->get($krsDetailId);
            if (!$detail) continue;
            $dataNilaiByMahasiswa[$detail->krs->mahasiswa_id] = $nilaiAngka;
        }

        try {
            $this->penilaianService->bulkInputNilai($kelasId, $dataNilaiByMahasiswa);
            return redirect()->back()->with('success', 'Nilai mahasiswa berhasil disimpan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

}
