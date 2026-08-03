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

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            ['No', 'NIDN', 'Nama Dosen', 'Email', 'Prodi', 'Fakultas', 'Jumlah Kelas']
        ], null, 'A1');

        $rowNum = 2;
        foreach ($dosenList as $idx => $dosen) {
            $prodiName = $dosen->prodi ? $dosen->prodi->nama : ($dosen->fakultas ? 'Semua Prodi' : 'Perguruan Tinggi');
            $fakultasName = $dosen->prodi ? ($dosen->prodi->fakultas->nama ?? '-') : ($dosen->fakultas ? $dosen->fakultas->nama : 'Perguruan Tinggi');

            $sheet->fromArray([
                $idx + 1,
                $dosen->nidn,
                $dosen->user->name ?? '-',
                $dosen->user->email ?? '-',
                $prodiName,
                $fakultasName,
                $dosen->kelas_count ?? 0,
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'dosen_export_' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120',
        ]);

        $file = $request->file('file');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $imported = 0;
        foreach ($sheetData as $rowIdx => $row) {
            if ($rowIdx === 1) continue; // Skip header

            $nidn = trim($row['A'] ?? '');
            $name = trim($row['B'] ?? '');
            $email = trim($row['C'] ?? '');
            $password = trim($row['D'] ?? 'password123');
            $prodiNameOrId = trim($row['E'] ?? '');

            if (empty($nidn) || empty($name)) continue;

            if (empty($email)) {
                $email = $nidn . '@dosen.siakad.com';
            }

            $prodiId = null;
            $fakultasId = null;

            if (!empty($prodiNameOrId)) {
                if (is_numeric($prodiNameOrId)) {
                    $prodi = Prodi::find($prodiNameOrId);
                } else {
                    $prodi = Prodi::where('nama', 'like', "%{$prodiNameOrId}%")->first();
                }
                if ($prodi) {
                    $prodiId = $prodi->id;
                    $fakultasId = $prodi->fakultas_id;
                }
            }
            if (!$prodiId && auth()->user()->fakultas_id) {
                $fakultasId = auth()->user()->fakultas_id;
            }

            DB::transaction(function() use ($nidn, $name, $email, $password, $prodiId, $fakultasId) {
                $user = User::updateOrCreate(
                    ['username' => $nidn],
                    [
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'password_plain' => $password,
                        'role' => 'dosen',
                    ]
                );

                Dosen::updateOrCreate(
                    ['nidn' => $nidn],
                    [
                        'user_id' => $user->id,
                        'prodi_id' => $prodiId,
                        'fakultas_id' => $fakultasId,
                    ]
                );
            });

            $imported++;
        }

        return redirect()->back()->with('success', "{$imported} data Dosen berhasil diimpor.");
    }
}

