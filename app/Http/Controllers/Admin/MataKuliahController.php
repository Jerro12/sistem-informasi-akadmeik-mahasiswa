<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fakultas;
use App\Models\MataKuliah;
use App\Models\Kurikulum;
use App\Models\Konsentrasi;
use App\Models\Prodi;
use App\Services\AkademikService;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    protected $akademikService;

    public function __construct(AkademikService $akademikService)
    {
        $this->akademikService = $akademikService;
    }

    public function index(Request $request)
    {
        $query = MataKuliah::with('prodi.fakultas');

        // 1. Filters (Semester, Prodi, Konsentrasi, Kurikulum)
        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        if ($request->filled('kosentrasi')) {
            $query->where('konsentrasi_id', $request->kosentrasi);
        }

        if ($request->filled('kurikulum')) {
            $query->where('kurikulum_id', $request->kurikulum);
        }

        // 2. Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                  ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }

        // 3. Faculty scoping for admin_fakultas
        // Shows: their faculty's MK + unassigned MK (prodi_id = NULL)
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->where(function($q) use ($fakultasId) {
                $q->whereHas('prodi', fn($q2) => $q2->where('fakultas_id', $fakultasId))
                  ->orWhereNull('prodi_id');
            });
        }

        // 4. Sorting
        $sortColumn = $request->get('sort', 'kode_mk');
        $sortDirection = $request->get('order', 'asc');
        
        $allowedSorts = ['kode_mk', 'nama_mk', 'sks', 'semester', 'created_at'];
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('kode_mk', 'asc');
        }

        // 5. Pagination
        $mataKuliah = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        
        // User info for view
        $isSuperAdmin = auth()->user()->isSuperAdmin();
        
        // Fakultas list for superadmin dropdown
        $fakultasList = $isSuperAdmin ? Fakultas::all() : collect();
        
        // Prodi list for dropdown (scoped)
        $prodiQuery = Prodi::with('fakultas');
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $prodiQuery->where('fakultas_id', $request->get('fakultas_scope'));
        }
        if (auth()->user()->role === 'admin_prodi' && auth()->user()->prodi_id) {
            $prodiQuery->where('id', auth()->user()->prodi_id);
        }
        $prodiList = $prodiQuery->get();

        // Kurikulum and Konsentrasi lists for dropdowns
        $kurikulumQuery = Kurikulum::query();
        $konsentrasiQuery = Konsentrasi::query();
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $kurikulumQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
            $konsentrasiQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        if (auth()->user()->role === 'admin_prodi' && auth()->user()->prodi_id) {
            $kurikulumQuery->where('prodi_id', auth()->user()->prodi_id);
            $konsentrasiQuery->where('prodi_id', auth()->user()->prodi_id);
        }
        $kurikulumList = $kurikulumQuery->get();
        $konsentrasiList = $konsentrasiQuery->get();

        return view('admin.mata-kuliah.index', compact('mataKuliah', 'prodiList', 'fakultasList', 'isSuperAdmin', 'kurikulumList', 'konsentrasiList'));
    }

    public function export(Request $request)
    {
        $query = MataKuliah::query();

        if ($request->filled('semester')) {
            $query->where('semester', $request->semester);
        }

        if ($request->filled('prodi')) {
            $query->where('prodi_id', $request->prodi);
        }

        if ($request->filled('kosentrasi')) {
            $query->where('konsentrasi_id', $request->kosentrasi);
        }

        if ($request->filled('kurikulum')) {
            $query->where('kurikulum_id', $request->kurikulum);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_mk', 'like', "%{$search}%")
                  ->orWhere('kode_mk', 'like', "%{$search}%");
            });
        }

        // Faculty scoping for export
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->where(function($q) use ($fakultasId) {
                $q->whereHas('prodi', fn($q2) => $q2->where('fakultas_id', $fakultasId))
                  ->orWhereNull('prodi_id');
            });
        }

        $sortColumn = $request->get('sort', 'kode_mk');
        $sortDirection = $request->get('order', 'asc');
        $allowedSorts = ['kode_mk', 'nama_mk', 'sks', 'semester', 'created_at'];
        
        if (in_array($sortColumn, $allowedSorts)) {
            $query->orderBy($sortColumn, $sortDirection);
        }

        return response()->streamDownload(function() use ($query) {
            $handle = fopen('php://output', 'w');
            
            fputs($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Kode MK', 'Nama Mata Kuliah', 'SKS', 'Semester', 'Prodi', 'Dibuat Pada']);

            $query->with('prodi')->chunk(500, function($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->kode_mk,
                        $row->nama_mk,
                        $row->sks,
                        $row->semester,
                        $row->prodi?->nama ?? '-',
                        $row->created_at,
                    ]);
                }
            });

            fclose($handle);
        }, 'data-mata-kuliah-' . date('Y-m-d-H-i') . '.csv');
    }

    public function store(Request $request)
    {
        $isAllFaculty = $request->prodi_id === 'all_faculty';

        $rules = [
            'nama_mk'  => 'required|string',
            'jenis'    => 'required|in:wajib,pilihan',
            'sks'      => 'required|integer|min:1',
            'semester' => 'required|integer|min:1',
            'kurikulum_id' => 'nullable|exists:kurikulum,id',
            'konsentrasi_id' => 'nullable|exists:konsentrasi,id',
        ];

        if ($isAllFaculty) {
            $rules['prodi_id'] = 'required|string';
            $rules['kode_mk'] = 'required|string';
        } else {
            $rules['prodi_id'] = 'nullable|exists:prodi,id';
            $rules['kode_mk'] = 'required|string|unique:mata_kuliah,kode_mk';
        }

        $validated = $request->validate($rules);

        $fakultasId = null;

        if ($isAllFaculty) {
            if (auth()->user()->role === 'admin_fakultas') {
                $fakultasId = auth()->user()->fakultas_id;
            } else if ($request->filled('fakultas_id')) {
                $fakultasId = $request->fakultas_id;
            }

            if (!$fakultasId) {
                return redirect()->back()->withErrors(['prodi_id' => 'Fakultas harus ditentukan untuk opsi Semua Prodi.'])->withInput();
            }

            $prodis = Prodi::where('fakultas_id', $fakultasId)->get();
            if ($prodis->isEmpty()) {
                return redirect()->back()->withErrors(['prodi_id' => 'Tidak ada program studi di fakultas ini.'])->withInput();
            }

            $toCreate = [];
            foreach ($prodis as $prodi) {
                // Generate initials
                $words = explode(' ', $prodi->nama);
                $initials = '';
                foreach ($words as $word) {
                    $initials .= strtoupper(substr($word, 0, 1));
                }
                $prodiCode = $initials;
                $generatedCode = $validated['kode_mk'] . '-' . $prodiCode;

                if (MataKuliah::where('kode_mk', $generatedCode)->exists()) {
                    return redirect()->back()->withErrors(['kode_mk' => "Kode mata kuliah {$generatedCode} sudah terpakai."])->withInput();
                }

                $toCreate[] = [
                    'prodi_id' => $prodi->id,
                    'kode_mk' => $generatedCode,
                ];
            }

            foreach ($toCreate as $item) {
                MataKuliah::create(array_merge($validated, [
                    'prodi_id' => $item['prodi_id'],
                    'kode_mk' => $item['kode_mk'],
                ]));
            }
        } else {
            // Auto-assign prodi for admin_fakultas if not provided
            if (empty($validated['prodi_id']) && $request->get('fakultas_scoped')) {
                $prodi = Prodi::where('fakultas_id', $request->get('fakultas_scope'))->first();
                if ($prodi) {
                    $validated['prodi_id'] = $prodi->id;
                }
            }
            MataKuliah::create($validated);
        }

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('master.mata_kuliah');
        if (class_exists(\App\Services\CacheService::class)) {
            $cacheService = app(\App\Services\CacheService::class);
            $cacheService->clearMataKuliahCache();
            if ($isAllFaculty && $fakultasId) {
                $cacheService->clearDashboardStats($fakultasId);
            } else {
                $cacheService->clearDashboardStats($request->get('fakultas_scope'));
            }
        }

        return redirect()->back()->with('success', 'Mata Kuliah berhasil ditambahkan');
    }

    public function update(Request $request, MataKuliah $mataKuliah)
    {
        $validated = $request->validate([
            'kode_mk'  => 'required|string|unique:mata_kuliah,kode_mk,' . $mataKuliah->id,
            'nama_mk'  => 'required|string',
            'jenis'    => 'required|in:wajib,pilihan',
            'sks'      => 'required|integer|min:1',
            'semester' => 'required|integer|min:1',
            'prodi_id' => 'nullable|exists:prodi,id',
            'kurikulum_id' => 'nullable|exists:kurikulum,id',
            'konsentrasi_id' => 'nullable|exists:konsentrasi,id',
        ]);
        $mataKuliah->update($validated);

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('master.mata_kuliah');
        if (class_exists(\App\Services\CacheService::class)) {
            $cacheService = app(\App\Services\CacheService::class);
            $cacheService->clearMataKuliahCache();
            $cacheService->clearDashboardStats($request->get('fakultas_scope'));
            if ($mataKuliah->prodi?->fakultas_id) {
                $cacheService->clearDashboardStats($mataKuliah->prodi->fakultas_id);
            }
        }

        return redirect()->back()->with('success', 'Mata Kuliah berhasil diupdate');
    }

    public function destroy(MataKuliah $mataKuliah)
    {
        $fakultasId = $mataKuliah->prodi?->fakultas_id;

        $mataKuliah->delete();

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('master.mata_kuliah');
        if (class_exists(\App\Services\CacheService::class)) {
            $cacheService = app(\App\Services\CacheService::class);
            $cacheService->clearMataKuliahCache();
            $cacheService->clearDashboardStats();
            if ($fakultasId) {
                $cacheService->clearDashboardStats($fakultasId);
            }
        }
        
        return redirect()->back()->with('success', 'Mata Kuliah berhasil dihapus beserta kelas terkait.');
    }
}

