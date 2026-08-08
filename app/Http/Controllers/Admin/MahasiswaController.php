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

        $list = $query->orderBy('nim')->get();

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->fromArray([
            ['No', 'NIM', 'Nama Mahasiswa', 'Email', 'Prodi', 'Angkatan', 'Semester', 'Status']
        ], null, 'A1');

        $rowNum = 2;
        foreach ($list as $idx => $mhs) {
            $sheet->fromArray([
                $idx + 1,
                $mhs->nim,
                $mhs->user->name ?? '-',
                $mhs->user->email ?? '-',
                $mhs->prodi->nama ?? '-',
                $mhs->angkatan,
                $mhs->semester_sekarang ?? 1,
                ucfirst($mhs->status),
            ], null, 'A' . $rowNum);
            $rowNum++;
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'data-mahasiswa-' . date('Y-m-d') . '.xlsx';

        return response()->streamDownload(function() use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $file = $request->file('file');
        $ext = strtolower($file->getClientOriginalExtension());
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->withErrors(['file' => 'File harus berupa format Excel (.xlsx, .xls) atau CSV (.csv).']);
        }

        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        $imported = 0;
        foreach ($sheetData as $rowIdx => $row) {
            if ($rowIdx === 1) continue; // Skip header

            $colA = trim($row['A'] ?? '');
            $colB = trim($row['B'] ?? '');

            if (empty($colA) && empty($colB)) continue;
            if (strtolower($colA) === 'nim' || strtolower($colB) === 'nim') continue;

            if (is_numeric($colA) || strtolower($colA) === 'no' || strtolower($colA) === 'no.') {
                $nim = $colB;
                $name = trim($row['C'] ?? '');
                $colD = trim($row['D'] ?? '');
                
                if (str_contains($colD, '@')) {
                    $email = $colD;
                    $password = trim($row['E'] ?? '') ?: 'password123';
                    $prodiNameOrId = trim($row['F'] ?? '');
                    $angkatan = (int) ($row['G'] ?? date('Y'));
                    $semester = (int) ($row['H'] ?? 1);
                } else {
                    $email = $nim . '@mahasiswa.siakad.com';
                    $password = $colD ?: 'password123';
                    $prodiNameOrId = trim($row['E'] ?? '');
                    $angkatan = (int) ($row['F'] ?? date('Y'));
                    $semester = (int) ($row['G'] ?? 1);
                }
            } else {
                $nim = $colA;
                $name = $colB;
                $colC = trim($row['C'] ?? '');

                if (str_contains($colC, '@')) {
                    $email = $colC;
                    $password = trim($row['D'] ?? '') ?: 'password123';
                    $prodiNameOrId = trim($row['E'] ?? '');
                    $angkatan = (int) ($row['F'] ?? date('Y'));
                    $semester = (int) ($row['G'] ?? 1);
                } else {
                    $email = $nim . '@mahasiswa.siakad.com';
                    $password = $colC ?: 'password123';
                    $prodiNameOrId = trim($row['D'] ?? '');
                    $angkatan = (int) ($row['E'] ?? date('Y'));
                    $semester = (int) ($row['F'] ?? 1);
                }
            }

            if (empty($nim) || empty($name)) continue;

            $prodi = null;
            if (!empty($prodiNameOrId)) {
                if (is_numeric($prodiNameOrId)) {
                    $prodi = Prodi::find($prodiNameOrId);
                } else {
                    $prodi = Prodi::where('nama', 'like', "%{$prodiNameOrId}%")->first();
                }
            }
            if (!$prodi) {
                $prodi = auth()->user()->prodi ?? (auth()->user()->fakultas_id ? Prodi::where('fakultas_id', auth()->user()->fakultas_id)->first() : Prodi::first());
            }

            if (!$prodi) continue;

            DB::transaction(function() use ($nim, $name, $email, $password, $prodi, $angkatan, $semester) {
                $user = User::updateOrCreate(
                    ['username' => $nim],
                    [
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'password_plain' => $password,
                        'role' => 'mahasiswa',
                    ]
                );

                Mahasiswa::updateOrCreate(
                    ['nim' => $nim],
                    [
                        'user_id' => $user->id,
                        'prodi_id' => $prodi->id,
                        'angkatan' => $angkatan > 2000 ? $angkatan : date('Y'),
                        'semester_sekarang' => $semester > 0 ? $semester : 1,
                        'status' => 'aktif',
                    ]
                );
            });

            $imported++;
        }

        return redirect()->back()->with('success', "{$imported} data Mahasiswa berhasil diimpor.");
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
