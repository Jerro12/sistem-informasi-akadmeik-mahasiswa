<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BimbinganSkripsi;
use App\Models\Skripsi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use Illuminate\Http\Request;

class BimbinganController extends Controller
{
    public function index(Request $request)
    {
        // Base query: bimbingan dengan relasi skripsi dan mahasiswa
        $query = BimbinganSkripsi::with([
            'skripsi.mahasiswa.user',
            'skripsi.mahasiswa.prodi',
            'dosen.user',
        ]);

        // Scope by prodi (admin_prodi)
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $prodiId = $request->get('prodi_scope');
            $query->whereHas('skripsi.mahasiswa', fn($q) => $q->where('prodi_id', $prodiId));
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('skripsi.mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Filter by dosen
        if ($request->filled('dosen_id')) {
            $query->where('dosen_id', $request->dosen_id);
        }

        // Filter by mahasiswa search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('skripsi.mahasiswa', function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $bimbinganList = $query->orderBy('tanggal_bimbingan', 'desc')
            ->paginate(20)
            ->withQueryString();

        // Stats - scoped
        $statsBase = BimbinganSkripsi::query();
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $statsBase->whereHas('skripsi.mahasiswa', fn($q) => $q->where('prodi_id', $request->get('prodi_scope')));
        }
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $statsBase->whereHas('skripsi.mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }

        $stats = [
            'total_bimbingan' => (clone $statsBase)->count(),
            'mahasiswa_aktif' => Skripsi::whereNotIn('status', ['selesai', 'ditolak'])
                ->when($request->get('prodi_scoped') && $request->get('prodi_scope'), function ($q) use ($request) {
                    $q->whereHas('mahasiswa', fn($q2) => $q2->where('prodi_id', $request->get('prodi_scope')));
                })
                ->count(),
            'bimbingan_bulan_ini' => (clone $statsBase)
                ->whereMonth('tanggal_bimbingan', now()->month)
                ->whereYear('tanggal_bimbingan', now()->year)
                ->count(),
        ];

        // Dosen list for filter (scoped)
        $dosenQuery = Dosen::with('user');
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $dosenQuery->where('prodi_id', $request->get('prodi_scope'));
        }
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        $dosenList = $dosenQuery->get();

        return view('admin.bimbingan.index', compact('bimbinganList', 'dosenList', 'stats'));
    }

    /**
     * Show monitoring per-mahasiswa (grouped view)
     */
    public function mahasiswaMonitor(Request $request)
    {
        $query = Skripsi::with(['mahasiswa.user', 'mahasiswa.prodi', 'pembimbing1.user', 'pembimbing2.user', 'bimbingan'])
            ->whereNotIn('status', ['selesai', 'ditolak']);

        // Scope by prodi (admin_prodi)
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $prodiId = $request->get('prodi_scope');
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $prodiId));
        }

        // Faculty scoping
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('mahasiswa', function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        $skripsiList = $query->withCount('bimbingan')
            ->orderByDesc('bimbingan_count')
            ->paginate(20)
            ->withQueryString();

        return view('admin.bimbingan.mahasiswa-monitor', compact('skripsiList'));
    }
}
