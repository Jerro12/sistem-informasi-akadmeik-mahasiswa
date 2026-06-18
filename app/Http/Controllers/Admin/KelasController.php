<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalKuliah;
use App\Services\AkademikService;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    protected $akademikService;

    public function __construct(AkademikService $akademikService)
    {
        $this->akademikService = $akademikService;
    }

    public function index(Request $request)
    {
        $query = \App\Models\Kelas::with(['mataKuliah', 'dosen.user', 'dosen.prodi', 'jadwal']);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('mataKuliah', fn($q2) => $q2->where('nama_mk', 'like', "%{$search}%")->orWhere('kode_mk', 'like', "%{$search}%"))
                  ->orWhereHas('dosen.user', fn($q3) => $q3->where('name', 'like', "%{$search}%"));
            });
        }

        // Filter by prodi
        if ($prodiId = $request->get('prodi_id')) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('prodi_id', $prodiId));
        }

        // Filter by semester
        if ($semester = $request->get('semester')) {
            $query->whereHas('mataKuliah', fn($q) => $q->where('semester', $semester));
        }

        // Faculty scoping for admin_fakultas (scope by dosen's prodi's fakultas)
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('dosen.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Sorting
        $sortColumn = $request->get('sort', 'nama_kelas');
        $sortDirection = $request->get('order', 'asc');

        if ($sortColumn === 'mata_kuliah') {
            $query->join('mata_kuliah', 'kelas.mata_kuliah_id', '=', 'mata_kuliah.id')
                  ->select('kelas.*')
                  ->orderBy('mata_kuliah.nama_mk', $sortDirection);
        } elseif ($sortColumn === 'dosen') {
            $query->join('dosen', 'kelas.dosen_id', '=', 'dosen.id')
                  ->join('users', 'dosen.user_id', '=', 'users.id')
                  ->select('kelas.*')
                  ->orderBy('users.name', $sortDirection);
        } elseif (in_array($sortColumn, ['nama_kelas', 'kapasitas'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('nama_kelas', 'asc');
        }

        $kelas = $query->paginate(config('siakad.pagination', 15))->withQueryString();
        $mataKuliah = $this->akademikService->getAllMataKuliah();
        
        // Scope dosen list for dropdown
        $dosenQuery = \App\Models\Dosen::with(['user', 'prodi']);
        $user = auth()->user();
        if ($user->role === 'admin_fakultas' && $user->fakultas_id) {
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $user->fakultas_id));
        } elseif ($user->role === 'admin_prodi' && $user->prodi_id && $user->prodi) {
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $user->prodi->fakultas_id));
        }
        $dosen = $dosenQuery->get();
        $prodisQuery = \App\Models\Prodi::orderBy('nama');
        $user = auth()->user();
        if ($user->role === 'admin_fakultas' && $user->fakultas_id) {
            $prodisQuery->where('fakultas_id', $user->fakultas_id);
        } elseif ($user->role === 'admin_prodi' && $user->prodi_id) {
            $prodisQuery->where('id', $user->prodi_id);
        }
        $prodis = $prodisQuery->get();

        $activeTA = \App\Models\TahunAkademik::where('is_active', true)->first();
        $activeSemester = $activeTA ? strtolower($activeTA->semester) : 'ganjil';
        
        return view('admin.kelas.index', compact('kelas', 'mataKuliah', 'dosen', 'prodis', 'activeSemester'));
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'dosen_id'       => 'required|exists:dosen,id',
            'nama_kelas'     => 'required|string',
            'kapasitas'      => 'nullable|integer|min:1',
            'hari'           => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'      => 'nullable|date_format:H:i',
            'jam_selesai'    => 'nullable|date_format:H:i',
            'ruangan'        => 'nullable|string|max:50',
        ]);
        
        // Check for conflicts if schedule is provided
        if (!empty($validated['hari']) && !empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $conflict = $this->checkConflict(null, $validated['hari'], $validated['jam_mulai'], $validated['jam_selesai'], $validated['ruangan'], $validated['dosen_id'], $validated['nama_kelas'], $validated['mata_kuliah_id']);
            if ($conflict) {
                return redirect()->back()->withInput()->with('error', "Gagal: " . $conflict);
            }
        }

        $kelas = $this->akademikService->createKelas($validated);
        
        // Create jadwal if provided
        if (!empty($validated['hari']) && !empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            JadwalKuliah::create([
                'kelas_id' => $kelas->id,
                'hari' => $validated['hari'],
                'jam_mulai' => $validated['jam_mulai'],
                'jam_selesai' => $validated['jam_selesai'],
                'ruangan' => $validated['ruangan'] ?? null,
            ]);
        }
        
        return redirect()->back()->with('success', 'Kelas berhasil ditambahkan');
    }

    public function update(Request $request, \App\Models\Kelas $kelas)
    {
        $validated = $request->validate([
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'dosen_id'       => 'required|exists:dosen,id',
            'nama_kelas'     => 'required|string',
            'kapasitas'      => 'nullable|integer|min:1',
            'hari'           => 'nullable|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'jam_mulai'      => 'nullable|date_format:H:i',
            'jam_selesai'    => 'nullable|date_format:H:i',
            'ruangan'        => 'nullable|string|max:50',
        ]);
        
        // Check for conflicts if schedule is provided
        if (!empty($validated['hari']) && !empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $conflict = $this->checkConflict($kelas->id, $validated['hari'], $validated['jam_mulai'], $validated['jam_selesai'], $validated['ruangan'], $validated['dosen_id'], $validated['nama_kelas'], $validated['mata_kuliah_id']);
            if ($conflict) {
                return redirect()->back()->withInput()->with('error', "Gagal: " . $conflict);
            }
        }

        $kelas->update([
            'mata_kuliah_id' => $validated['mata_kuliah_id'],
            'dosen_id' => $validated['dosen_id'],
            'nama_kelas' => $validated['nama_kelas'],
            'kapasitas' => $validated['kapasitas'],
        ]);
        
        // Update or create jadwal with notification
        if (!empty($validated['hari']) && !empty($validated['jam_mulai']) && !empty($validated['jam_selesai'])) {
            $oldJadwal = $kelas->jadwal()->first();
            
            // Track changes
            $changes = [];
            if ($oldJadwal) {
                if ($oldJadwal->hari !== $validated['hari']) {
                    $changes['hari'] = ['old' => $oldJadwal->hari, 'new' => $validated['hari']];
                }
                $oldJam = \Carbon\Carbon::parse($oldJadwal->jam_mulai)->format('H:i') . '-' . \Carbon\Carbon::parse($oldJadwal->jam_selesai)->format('H:i');
                $newJam = $validated['jam_mulai'] . '-' . $validated['jam_selesai'];
                if ($oldJam !== $newJam) {
                    $changes['jam'] = ['old' => $oldJam, 'new' => $newJam];
                }
                if (($oldJadwal->ruangan ?? '') !== ($validated['ruangan'] ?? '')) {
                    $changes['ruangan'] = ['old' => $oldJadwal->ruangan ?? '-', 'new' => $validated['ruangan'] ?? '-'];
                }
            }
            
            $jadwal = $kelas->jadwal()->updateOrCreate(
                ['kelas_id' => $kelas->id],
                [
                    'hari' => $validated['hari'],
                    'jam_mulai' => $validated['jam_mulai'],
                    'jam_selesai' => $validated['jam_selesai'],
                    'ruangan' => $validated['ruangan'] ?? null,
                ]
            );
            
            // Send notification if there are changes
            if (!empty($changes)) {
                $kelas->load('mataKuliah');
                $notificationService = app(\App\Services\NotificationService::class);
                $count = $notificationService->notifyJadwalChange($kelas, $jadwal, $changes);
                
                if ($count > 0) {
                    return redirect()->back()->with('success', "Kelas berhasil diupdate. Notifikasi terkirim ke {$count} mahasiswa.");
                }
            }
        }
        
        return redirect()->back()->with('success', 'Kelas berhasil diupdate');
    }

    public function destroy(\App\Models\Kelas $kelas)
    {
        $kelas->delete();
        return redirect()->back()->with('success', 'Kelas berhasil dihapus');
    }

    private function checkConflict($kelasId, $hari, $jamMulai, $jamSelesai, $ruangan, $dosenId, $namaKelas, $mkId)
    {
        $mk = \App\Models\MataKuliah::find($mkId);
        
        $conflicts = \App\Models\JadwalKuliah::where('hari', $hari)
            ->where(function ($q) use ($jamMulai, $jamSelesai) {
                $q->where('jam_mulai', '<', $jamSelesai)
                  ->where('jam_selesai', '>', $jamMulai);
            })
            ->whereHas('kelas', function ($q) use ($kelasId) {
                if ($kelasId) $q->where('id', '!=', $kelasId);
            })
            ->get();

        foreach ($conflicts as $conflict) {
            // 1. Dosen Conflict
            if ($conflict->kelas->dosen_id == $dosenId) {
                return "Dosen sudah memiliki jadwal mengajar di kelas {$conflict->kelas->nama_kelas} ({$conflict->kelas->mataKuliah->nama_mk}) pada jam tersebut.";
            }
            
            // 2. Room Conflict
            if ($ruangan && $conflict->ruangan === $ruangan) {
                return "Ruangan {$ruangan} sudah digunakan oleh kelas {$conflict->kelas->nama_kelas} ({$conflict->kelas->mataKuliah->nama_mk}) pada jam tersebut.";
            }
            
            // 3. Class Group Conflict
            if ($conflict->kelas->nama_kelas === $namaKelas && 
                $conflict->kelas->mataKuliah->prodi_id === $mk->prodi_id && 
                $conflict->kelas->mataKuliah->semester === $mk->semester) {
                return "Kelompok Kelas {$namaKelas} (Semester {$mk->semester}) sudah memiliki jadwal mata kuliah {$conflict->kelas->mataKuliah->nama_mk} pada jam tersebut.";
            }
        }
        
        return null;
    }

    public function cetak(Request $request)
    {
        $request->validate([
            'prodi_id' => 'required|exists:prodi,id',
            'semester_type' => 'required|in:ganjil,genap',
        ]);

        $prodi = \App\Models\Prodi::with('fakultas')->findOrFail($request->prodi_id);
        $semesterType = $request->semester_type;
        $semesters = $semesterType === 'ganjil' ? [1, 3, 5, 7] : [2, 4, 6, 8];
        $activeYear = \App\Models\TahunAkademik::where('is_active', true)->first();

        // Fetch JADWAL instead of KELAS
        $jadwalList = \App\Models\JadwalKuliah::with(['kelas.mataKuliah', 'kelas.dosen.user'])
            ->whereHas('kelas.mataKuliah', function($q) use ($request, $semesters) {
                $q->where('prodi_id', $request->prodi_id)
                  ->whereIn('semester', $semesters);
            })
            ->whereHas('kelas', function($q) use ($activeYear) {
                $q->where('tahun_akademik_id', $activeYear?->id);
            })
            ->get();

        // Group by day
        $groupedJadwal = $jadwalList->groupBy('hari');

        // Sorted Days
        $dayOrder = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $sortedGroupedJadwal = collect();
        foreach ($dayOrder as $day) {
            if ($groupedJadwal->has($day)) {
                $sortedGroupedJadwal[$day] = $groupedJadwal[$day]->sortBy('jam_mulai')->values();
            }
        }

        return view('admin.kelas.cetak', [
            'prodi' => $prodi,
            'semesterType' => $semesterType,
            'activeYear' => $activeYear,
            'groupedJadwal' => $sortedGroupedJadwal,
        ]);
    }
}

