<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Dosen;
use App\Services\AkademikCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\Kurikulum;
use App\Models\Konsentrasi;

class MahasiswaController extends Controller
{
    protected AkademikCalculationService $calculationService;

    public function __construct(AkademikCalculationService $calculationService)
    {
        $this->calculationService = $calculationService;
    }

    public function index(Request $request)
    {
        $query = Mahasiswa::with(['user', 'prodi.fakultas', 'dosenPa.user']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by fakultas
        if ($fakultasId = $request->get('fakultas_id')) {
            $query->whereHas('prodi', function ($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            });
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $query->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }

        // Prodi scoping for admin_prodi (must override manual prodi_id filter)
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $query->where('prodi_id', $request->get('prodi_scope'));
        }

        // Filter by prodi
        if ($prodiId = $request->get('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }

        // Filter by angkatan
        if ($angkatan = $request->get('angkatan')) {
            $query->where('angkatan', $angkatan);
        }

        // Variable Sorting
        $sortColumn = $request->get('sort', 'nim');
        $sortDirection = $request->get('order', 'asc');

        if ($sortColumn === 'name') {
            $query->join('users', 'mahasiswa.user_id', '=', 'users.id')
                  ->select('mahasiswa.*')
                  ->orderBy('users.name', $sortDirection);
        } elseif ($sortColumn === 'prodi') {
             $query->join('prodi', 'mahasiswa.prodi_id', '=', 'prodi.id')
                   ->select('mahasiswa.*')
                   ->orderBy('prodi.nama', $sortDirection);
        } else {
             $query->orderBy($sortColumn, $sortDirection);
        }

        $mahasiswa = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        
        $user = auth()->user();
        $fakultasList = \App\Models\Fakultas::orderBy('nama')->get();
        
        $prodiQuery = Prodi::with('fakultas')->orderBy('nama');
        $dosenQuery = Dosen::with('user');
        $kurikulumQuery = Kurikulum::query();
        $konsentrasiQuery = Konsentrasi::query();

        // 1. Scoping by Faculty (for admin_fakultas or general scoped request)
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $prodiQuery->where('fakultas_id', $fakultasId);
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
            $kurikulumQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
            $konsentrasiQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // 2. Scoping by Prodi (for admin_prodi)
        $prodiScopeId = $request->get('prodi_scoped') ? $request->get('prodi_scope') : null;
        if (!$prodiScopeId && $user->role === 'admin_prodi' && $user->prodi_id) {
            $prodiScopeId = $user->prodi_id;
        }

        if ($prodiScopeId) {
            $prodiQuery->where('id', $prodiScopeId);
            $dosenQuery->where('prodi_id', $prodiScopeId);
            $kurikulumQuery->where('prodi_id', $prodiScopeId);
            $konsentrasiQuery->where('prodi_id', $prodiScopeId);
        }

        $prodiList = $prodiQuery->get();
        $dosenList = $dosenQuery->get();
        $kurikulumList = $kurikulumQuery->get();
        $konsentrasiList = $konsentrasiQuery->get();
        
        $angkatanList = Mahasiswa::distinct()->pluck('angkatan')->sort()->reverse();

        return view('admin.mahasiswa.index', compact('mahasiswa', 'fakultasList', 'prodiList', 'angkatanList', 'dosenList', 'kurikulumList', 'konsentrasiList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'nim' => 'required|string|unique:mahasiswa,nim',
            'prodi_id' => 'required|exists:prodi,id',
            'angkatan' => 'required|numeric|min:2000|max:' . (date('Y') + 1),
            'semester_sekarang' => 'required|integer|min:1|max:14',
            'dosen_pa_id' => 'nullable|exists:dosen,id',
            'kurikulum_id' => 'nullable|exists:kurikulum,id',
        ]);

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['nim'],
                'email' => $validated['nim'] . '@mahasiswa.siakad.com',
                'password' => Hash::make($validated['password']),
                'password_plain' => $validated['password'],
                'role' => 'mahasiswa',
            ]);

            Mahasiswa::create([
                'user_id' => $user->id,
                'nim' => $validated['nim'],
                'prodi_id' => $validated['prodi_id'],
                'angkatan' => $validated['angkatan'],
                'semester_sekarang' => $validated['semester_sekarang'],
                'dosen_pa_id' => $validated['dosen_pa_id'] ?? null,
                'status' => 'aktif',
                'kurikulum_id' => $validated['kurikulum_id'] ?? null,
            ]);
        });

        return back()->with('success', 'Mahasiswa berhasil ditambahkan.');
    }

    public function update(Request $request, Mahasiswa $mahasiswa)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nim' => 'required|string|unique:mahasiswa,nim,' . $mahasiswa->id . '|unique:users,username,' . $mahasiswa->user_id,
            'prodi_id' => 'required|exists:prodi,id',
            'angkatan' => 'required|numeric',
            'semester_sekarang' => 'required|integer|min:1|max:14',
            'dosen_pa_id' => 'nullable|exists:dosen,id',
            'status' => 'required|in:aktif,cuti,lulus,do',
            'password' => 'nullable|string|min:8',
            'kurikulum_id' => 'nullable|exists:kurikulum,id',
        ]);

        DB::transaction(function () use ($validated, $mahasiswa) {
            $userUpdate = [
                'name' => $validated['name'],
                'username' => $validated['nim'],
                'email' => $validated['nim'] . '@mahasiswa.siakad.com',
            ];

            if (!empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
                $userUpdate['password_plain'] = $validated['password'];
            }

            $mahasiswa->user->update($userUpdate);

            $mahasiswa->update([
                'nim' => $validated['nim'],
                'prodi_id' => $validated['prodi_id'],
                'angkatan' => $validated['angkatan'],
                'semester_sekarang' => $validated['semester_sekarang'],
                'dosen_pa_id' => $validated['dosen_pa_id'] ?? null,
                'status' => $validated['status'],
                'kurikulum_id' => $validated['kurikulum_id'] ?? null,
            ]);
        });

        return back()->with('success', 'Mahasiswa berhasil diupdate.');
    }

    public function destroy(Mahasiswa $mahasiswa)
    {
        DB::transaction(function () use ($mahasiswa) {
            $userId = $mahasiswa->user_id;
            $mahasiswa->delete();
            User::destroy($userId);
        });

        return back()->with('success', 'Mahasiswa berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $query = Mahasiswa::with(['user', 'prodi']);

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nim', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }
        if ($fakultasId = $request->get('fakultas_id')) {
            $query->whereHas('prodi', function ($q) use ($fakultasId) {
                $q->where('fakultas_id', $fakultasId);
            });
        }
        if ($prodiId = $request->get('prodi_id')) {
            $query->where('prodi_id', $prodiId);
        }
        if ($angkatan = $request->get('angkatan')) {
            $query->where('angkatan', $angkatan);
        }

        $query->orderBy('nim');

        return response()->streamDownload(function() use ($query) {
            $handle = fopen('php://output', 'w');
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['NIM', 'Nama Mahasiswa', 'Prodi', 'Angkatan', 'Status', 'IPK']);

            $query->chunk(500, function($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->nim,
                        $row->user->name ?? '-',
                        $row->prodi->nama ?? '-',
                        $row->angkatan,
                        $row->status,
                        $row->ipk ?? 0,
                    ]);
                }
            });
            fclose($handle);
        }, 'data-mahasiswa-' . date('Y-m-d') . '.csv');
    }

    public function show(Mahasiswa $mahasiswa)
    {
        $mahasiswa->load(['user', 'prodi.fakultas', 'krs.tahunAkademik', 'krs.krsDetail.kelas.mataKuliah']);
        
        $ipkData = $this->calculationService->calculateIPK($mahasiswa);
        $ipsHistory = $this->calculationService->getIPSHistory($mahasiswa);
        $gradeDistribution = $this->calculationService->getGradeDistribution($mahasiswa);

        return view('admin.mahasiswa.show', compact('mahasiswa', 'ipkData', 'ipsHistory', 'gradeDistribution'));
    }
}
