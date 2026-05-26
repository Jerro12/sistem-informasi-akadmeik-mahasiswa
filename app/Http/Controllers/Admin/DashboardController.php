<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Nilai;
use App\Models\TahunAkademik;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $isSuperAdmin = $user->isSuperAdmin();
        $fakultasId = $user->fakultas_id;
        
        // For admin_fakultas, get their fakultas info
        $fakultas = null;
        if (!$isSuperAdmin && $fakultasId) {
            $fakultas = Fakultas::find($fakultasId);
        }

        // Build scoped queries
        $prodiQuery = Prodi::query();
        $mahasiswaQuery = Mahasiswa::query();
        $dosenQuery = Dosen::query();
        $mataKuliahQuery = MataKuliah::query();
        $kelasQuery = Kelas::query();
        $nilaiQuery = Nilai::query();
        
        if ($user->role === 'admin_prodi') {
            $prodiId = $user->prodi_id;
            $prodiQuery->where('id', $prodiId);
            $mahasiswaQuery->where('prodi_id', $prodiId);
            $dosenQuery->where('prodi_id', $prodiId);
            $mataKuliahQuery->where('prodi_id', $prodiId);
            $kelasQuery->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $prodiId));
            $nilaiQuery->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $prodiId));
        } elseif (!$isSuperAdmin && $fakultasId) {
            $prodiQuery->where('fakultas_id', $fakultasId);
            $mahasiswaQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
            $mataKuliahQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
            $kelasQuery->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
            $nilaiQuery->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Basic counts (scoped)
        $stats = [
            'prodi' => (clone $prodiQuery)->count(),
            'mahasiswa' => (clone $mahasiswaQuery)->count(),
            'dosen' => (clone $dosenQuery)->count(),
            'mata_kuliah' => (clone $mataKuliahQuery)->count(),
            'kelas' => (clone $kelasQuery)->count(),
        ];
        
        // Only show fakultas count for superadmin
        if ($isSuperAdmin) {
            $stats['fakultas'] = Fakultas::count();
        }

        // Grade distribution (scoped)
        $gradeDistribution = (clone $nilaiQuery)
            ->selectRaw('nilai_huruf, COUNT(*) as count')
            ->groupBy('nilai_huruf')
            ->pluck('count', 'nilai_huruf')
            ->toArray();

        // Active academic year
        $activeYear = TahunAkademik::where('is_active', true)->first();

        // Per-prodi student count (scoped)
        $prodiStatsQuery = Prodi::withCount('mahasiswa');
        if ($user->role === 'admin_prodi') {
            $prodiStatsQuery->where('id', $user->prodi_id);
        } elseif (!$isSuperAdmin && $fakultasId) {
            $prodiStatsQuery->where('fakultas_id', $fakultasId);
        }
        $prodiStats = $prodiStatsQuery
            ->orderBy('mahasiswa_count', 'desc')
            ->take(10)
            ->get();

        // Per-prodi dosen count (scoped) - NEW for admin_fakultas
        $dosenPerProdiQuery = Prodi::withCount('dosen');
        if ($user->role === 'admin_prodi') {
            $dosenPerProdiQuery->where('id', $user->prodi_id);
        } elseif (!$isSuperAdmin && $fakultasId) {
            $dosenPerProdiQuery->where('fakultas_id', $fakultasId);
        }
        $dosenPerProdi = $dosenPerProdiQuery
            ->orderBy('dosen_count', 'desc')
            ->take(10)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats', 'gradeDistribution', 'activeYear', 
            'prodiStats', 'dosenPerProdi', 'isSuperAdmin', 'fakultas'
        ));
    }
}

