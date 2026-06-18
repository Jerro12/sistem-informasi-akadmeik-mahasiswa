<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use App\Models\Prodi;
use App\Models\Kelas;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    public function index(Request $request)
    {
        $query = Dosen::with(['user', 'prodi.fakultas', 'fakultas'])
            ->withCount('kelas');

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nidn', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by prodi/level
        if ($prodiId = $request->get('prodi_id')) {
            if ($prodiId === 'perguruan_tinggi' || $prodiId === 'pt') {
                $query->whereNull('dosen.prodi_id')->whereNull('dosen.fakultas_id');
            } elseif (str_starts_with($prodiId, 'fakultas_')) {
                $fId = (int) str_replace('fakultas_', '', $prodiId);
                $query->where('dosen.fakultas_id', $fId);
            } else {
                $query->where('dosen.prodi_id', $prodiId);
            }
        }

        // Faculty scoping
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('prodi', fn($q2) => $q2->where('fakultas_id', $request->get('fakultas_scope')))
                  ->orWhere('fakultas_id', $request->get('fakultas_scope'));
            });
        }

        // Prodi scoping for admin_prodi
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $query->where('dosen.prodi_id', $request->get('prodi_scope'));
        }

        // Sorting
        $sortColumn = $request->get('sort', 'nidn');
        $sortDirection = $request->get('order', 'asc');

        if ($sortColumn === 'name') {
            $query->leftJoin('users', 'dosen.user_id', '=', 'users.id')
                  ->select('dosen.*')
                  ->orderBy('users.name', $sortDirection);
        } elseif ($sortColumn === 'prodi') {
             $query->leftJoin('prodi', 'dosen.prodi_id', '=', 'prodi.id')
                   ->leftJoin('fakultas', 'dosen.fakultas_id', '=', 'fakultas.id')
                   ->select('dosen.*')
                   ->orderByRaw('COALESCE(prodi.nama, fakultas.nama, "Perguruan Tinggi") ' . $sortDirection);
        } else {
             $query->orderBy('nidn', $sortDirection);
        }

        $dosen = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        
        // Prodi list scoped by faculty
        $prodiQuery = Prodi::with('fakultas');
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $prodiQuery->where('fakultas_id', $request->get('fakultas_scope'));
        }
        $prodiList = $prodiQuery->get();

        // Fakultas list scoped by faculty
        $fakultasQuery = \App\Models\Fakultas::query();
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasQuery->where('id', $request->get('fakultas_scope'));
        }
        $fakultasList = $fakultasQuery->get();

        return view('admin.dosen.index', compact('dosen', 'prodiList', 'fakultasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8',
            'nidn' => 'required|string|unique:dosen,nidn|unique:users,username',
            'prodi_id' => 'nullable|string',
        ]);

        $prodiId = null;
        $fakultasId = null;

        if ($request->filled('prodi_id')) {
            $val = $request->input('prodi_id');
            if (str_starts_with($val, 'fakultas_')) {
                $fakultasId = (int) str_replace('fakultas_', '', $val);
                if (!\App\Models\Fakultas::where('id', $fakultasId)->exists()) {
                    return back()->withErrors(['prodi_id' => 'Fakultas tidak valid.']);
                }
            } else {
                $prodiId = (int) $val;
                $prodi = Prodi::find($prodiId);
                if (!$prodi) {
                    return back()->withErrors(['prodi_id' => 'Prodi tidak valid.']);
                }
                $fakultasId = $prodi->fakultas_id;
            }
        }

        DB::transaction(function () use ($validated, $prodiId, $fakultasId) {
            $user = User::create([
                'name' => $validated['name'],
                'username' => $validated['nidn'],
                'email' => $validated['nidn'] . '@dosen.siakad.com',
                'password' => Hash::make($validated['password']),
                'password_plain' => $validated['password'],
                'role' => 'dosen',
                'prodi_id' => $prodiId,
                'fakultas_id' => $fakultasId,
            ]);

            Dosen::create([
                'user_id' => $user->id,
                'nidn' => $validated['nidn'],
                'prodi_id' => $prodiId,
                'fakultas_id' => $fakultasId,
            ]);
        });

        return back()->with('success', 'Dosen berhasil ditambahkan.');
    }

    public function update(Request $request, Dosen $dosen)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'nidn' => 'required|string|unique:dosen,nidn,' . $dosen->id . '|unique:users,username,' . $dosen->user_id,
            'prodi_id' => 'nullable|string',
            'password' => 'nullable|string|min:8',
        ]);

        $prodiId = null;
        $fakultasId = null;

        if ($request->filled('prodi_id')) {
            $val = $request->input('prodi_id');
            if (str_starts_with($val, 'fakultas_')) {
                $fakultasId = (int) str_replace('fakultas_', '', $val);
                if (!\App\Models\Fakultas::where('id', $fakultasId)->exists()) {
                    return back()->withErrors(['prodi_id' => 'Fakultas tidak valid.']);
                }
            } else {
                $prodiId = (int) $val;
                $prodi = Prodi::find($prodiId);
                if (!$prodi) {
                    return back()->withErrors(['prodi_id' => 'Prodi tidak valid.']);
                }
                $fakultasId = $prodi->fakultas_id;
            }
        }

        DB::transaction(function () use ($validated, $dosen, $prodiId, $fakultasId) {
            $userUpdate = [
                'name' => $validated['name'],
                'username' => $validated['nidn'],
                'email' => $validated['nidn'] . '@dosen.siakad.com',
                'prodi_id' => $prodiId,
                'fakultas_id' => $fakultasId,
            ];

            if (!empty($validated['password'])) {
                $userUpdate['password'] = Hash::make($validated['password']);
                $userUpdate['password_plain'] = $validated['password'];
            }

            $dosen->user->update($userUpdate);

            $dosen->update([
                'nidn' => $validated['nidn'],
                'prodi_id' => $prodiId,
                'fakultas_id' => $fakultasId,
            ]);
        });

        return back()->with('success', 'Dosen berhasil diupdate.');
    }

    public function destroy(Dosen $dosen)
    {
        DB::transaction(function () use ($dosen) {
            // Safe nullification of academic advising relationships
            $dosen->mahasiswaBimbingan()->update(['dosen_pa_id' => null]);

            $userId = $dosen->user_id;
            $dosen->delete();
            User::destroy($userId);
        });

        return back()->with('success', 'Dosen berhasil dihapus.');
    }

    public function show(Dosen $dosen)
    {
        $dosen->load(['user', 'prodi.fakultas', 'fakultas', 'kelas.mataKuliah', 'kelas.krsDetail']);
        
        // Paginate kelas (4 per page)
        $kelasIds = $dosen->kelas()->pluck('id');
        $teachingLoad = $dosen->kelas()->with(['mataKuliah', 'krsDetail'])->paginate(4);

        // Calculate totals for stats (based on all classes, not just paginated ones)
        $totalSks = $dosen->kelas->sum(fn($k) => $k->mataKuliah?->sks ?? 0);
        $totalStudents = \App\Models\KrsDetail::whereIn('kelas_id', $kelasIds)->count();

        return view('admin.dosen.show', compact('dosen', 'teachingLoad', 'totalSks', 'totalStudents'));
    }

    public function export(Request $request)
    {
        $query = Dosen::with(['user', 'prodi.fakultas', 'fakultas'])->withCount('kelas');

        // Faculty scoping
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $query->where(function ($q) use ($request) {
                $q->whereHas('prodi', fn($q2) => $q2->where('fakultas_id', $request->get('fakultas_scope')))
                  ->orWhere('fakultas_id', $request->get('fakultas_scope'));
            });
        }

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nidn', 'like', "%{$search}%")
                    ->orWhereHas('user', fn($q2) => $q2->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by prodi/level
        if ($prodiId = $request->get('prodi_id')) {
            if ($prodiId === 'perguruan_tinggi' || $prodiId === 'pt') {
                $query->whereNull('dosen.prodi_id')->whereNull('dosen.fakultas_id');
            } elseif (str_starts_with($prodiId, 'fakultas_')) {
                $fId = (int) str_replace('fakultas_', '', $prodiId);
                $query->where('dosen.fakultas_id', $fId);
            } else {
                $query->where('dosen.prodi_id', $prodiId);
            }
        }

        $dosenList = $query->orderBy('nidn')->get();

        // Generate HTML table for export
        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<thead><tr><th>No</th><th>NIDN</th><th>Nama</th><th>Email</th><th>Prodi</th><th>Fakultas</th><th>Jumlah Kelas</th></tr></thead>';
        $html .= '<tbody>';
        
        foreach ($dosenList as $idx => $dosen) {
            $prodiName = $dosen->prodi ? $dosen->prodi->nama : ($dosen->fakultas ? 'Semua Prodi' : 'Perguruan Tinggi');
            $fakultasName = $dosen->prodi ? ($dosen->prodi->fakultas->nama ?? '-') : ($dosen->fakultas ? $dosen->fakultas->nama : 'Perguruan Tinggi');
            
            $html .= '<tr>';
            $html .= '<td>' . ($idx + 1) . '</td>';
            $html .= '<td>' . $dosen->nidn . '</td>';
            $html .= '<td>' . $dosen->user->name . '</td>';
            $html .= '<td>' . $dosen->user->email . '</td>';
            $html .= '<td>' . $prodiName . '</td>';
            $html .= '<td>' . $fakultasName . '</td>';
            $html .= '<td>' . $dosen->kelas_count . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="dosen_export_' . date('Y-m-d') . '.xls"');
    }
}

