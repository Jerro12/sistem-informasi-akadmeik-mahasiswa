<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Skripsi;
use App\Models\Dosen;
use App\Models\PendaftaranUjian;
use App\Models\SyaratUjianUpload;
use Illuminate\Http\Request;

class SkripsiController extends Controller
{
    public function index(Request $request)
    {
        $query = Skripsi::with(['mahasiswa.user', 'mahasiswa.prodi', 'pembimbing1.user', 'pembimbing2.user']);

        // Scope by prodi (admin_prodi)
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $prodiId = $request->get('prodi_scope');
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $prodiId));
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhereHas('mahasiswa.user', fn($q) => $q->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('mahasiswa', fn($q) => $q->where('nim', 'like', "%{$search}%"));
            });
        }

        // Sorting
        $sortColumn = $request->get('sort', 'created_at');
        $sortDirection = $request->get('order', 'desc');

        if ($sortColumn === 'mahasiswa_name') {
            $query->join('mahasiswa', 'skripsi.mahasiswa_id', '=', 'mahasiswa.id')
                  ->join('users', 'mahasiswa.user_id', '=', 'users.id')
                  ->select('skripsi.*')
                  ->orderBy('users.name', $sortDirection);
        } elseif ($sortColumn === 'mahasiswa_nim') {
            $query->join('mahasiswa', 'skripsi.mahasiswa_id', '=', 'mahasiswa.id')
                  ->select('skripsi.*')
                  ->orderBy('mahasiswa.nim', $sortDirection);
        } elseif ($sortColumn === 'pembimbing_name') {
            $query->leftJoin('dosen', 'skripsi.pembimbing1_id', '=', 'dosen.id')
                  ->leftJoin('users', 'dosen.user_id', '=', 'users.id')
                  ->select('skripsi.*')
                  ->orderBy('users.name', $sortDirection);
        } elseif (in_array($sortColumn, ['judul', 'status', 'created_at'])) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('created_at', 'desc');
        }

        $skripsiList = $query->paginate(20)->withQueryString();
        
        // Scope dosen list for dropdown
        $dosenQuery = Dosen::with('user');
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $dosenQuery->where('prodi_id', $request->get('prodi_scope'));
        }
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $dosenQuery->whereHas('prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        $dosenList = $dosenQuery->get();
        $statusList = Skripsi::getStatusList();

        // Stats - also scoped
        $statsQuery = Skripsi::query();
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $statsQuery->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $request->get('prodi_scope')));
        }
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $statsQuery->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $request->get('fakultas_scope')));
        }
        $stats = [
            'total' => (clone $statsQuery)->count(),
            'aktif' => (clone $statsQuery)->active()->count(),
            'menunggu_pembimbing' => (clone $statsQuery)->whereNull('pembimbing1_id')->count(),
            'selesai' => (clone $statsQuery)->where('status', Skripsi::STATUS_SELESAI)->count(),
        ];

        return view('admin.skripsi.index', compact('skripsiList', 'dosenList', 'statusList', 'stats'));
    }

    public function show(Skripsi $skripsi)
    {
        $skripsi->load(['mahasiswa.user', 'pembimbing1.user', 'pembimbing2.user', 'bimbingan.dosen.user']);
        $dosenList = Dosen::with('user')->get();

        return view('admin.skripsi.show', compact('skripsi', 'dosenList'));
    }

    public function assignPembimbing(Request $request, Skripsi $skripsi)
    {
        $validated = $request->validate([
            'pembimbing1_id' => 'required|exists:dosen,id',
            'pembimbing2_id' => 'nullable|exists:dosen,id|different:pembimbing1_id',
        ]);

        $skripsi->update([
            'pembimbing1_id' => $validated['pembimbing1_id'],
            'pembimbing2_id' => $validated['pembimbing2_id'] ?? null,
            'status' => Skripsi::STATUS_DITERIMA,
            'tanggal_acc_judul' => now(),
        ]);

        return redirect()->back()->with('success', 'Pembimbing berhasil ditentukan');
    }

    public function updateStatus(Request $request, Skripsi $skripsi)
    {
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', array_keys(Skripsi::getStatusList())),
            'catatan_admin' => 'nullable|string',
        ]);

        $updateData = ['status' => $validated['status']];

        if (!empty($validated['catatan_admin'])) {
            $updateData['catatan_admin'] = $validated['catatan_admin'];
        }

        // Update milestone dates based on status
        $dateFields = [
            Skripsi::STATUS_DITERIMA => 'tanggal_acc_judul',
            Skripsi::STATUS_SEMINAR_PROPOSAL => 'tanggal_seminar_proposal',
            Skripsi::STATUS_SEMINAR_HASIL => 'tanggal_seminar_hasil',
            Skripsi::STATUS_SIDANG => 'tanggal_sidang',
            Skripsi::STATUS_SELESAI => 'tanggal_selesai',
        ];

        if (isset($dateFields[$validated['status']]) && empty($skripsi->{$dateFields[$validated['status']]})) {
            $updateData[$dateFields[$validated['status']]] = now();
        }

        $skripsi->update($updateData);

        return redirect()->back()->with('success', 'Status skripsi berhasil diupdate');
    }

    public function updateNilai(Request $request, Skripsi $skripsi)
    {
        $validated = $request->validate([
            'nilai_akhir' => 'required|numeric|min:0|max:100',
            'nilai_huruf' => 'required|in:A,B+,B,C+,C,D,E',
        ]);

        $skripsi->update([
            'nilai_akhir' => $validated['nilai_akhir'],
            'nilai_huruf' => $validated['nilai_huruf'],
            'status' => Skripsi::STATUS_SELESAI,
            'tanggal_selesai' => now(),
        ]);

        return redirect()->back()->with('success', 'Nilai skripsi berhasil disimpan');
    }

    // --- PEMDAFTARAN UJIAN MANAGEMENT (PRODI ADMIN) ---

    public function pendaftaranUjianList(Request $request)
    {
        $query = PendaftaranUjian::with(['mahasiswa.user', 'mahasiswa.prodi', 'skripsi']);

        // Scope by prodi (admin_prodi)
        if ($request->get('prodi_scoped') && $request->get('prodi_scope')) {
            $prodiId = $request->get('prodi_scope');
            $query->whereHas('mahasiswa', fn($q) => $q->where('prodi_id', $prodiId));
        }

        // Faculty scoping for admin_fakultas
        if ($request->get('fakultas_scoped') && $request->get('fakultas_scope')) {
            $fakultasId = $request->get('fakultas_scope');
            $query->whereHas('mahasiswa.prodi', fn($q) => $q->where('fakultas_id', $fakultasId));
        }

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('jenis_ujian')) {
            $query->where('jenis_ujian', $request->jenis_ujian);
        }

        $ujianList = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.skripsi.pendaftaran-ujian-list', compact('ujianList'));
    }

    public function pendaftaranUjianShow(PendaftaranUjian $ujian)
    {
        $ujian->load(['mahasiswa.user', 'mahasiswa.prodi', 'skripsi', 'syaratUpload', 'penguji1.user', 'penguji2.user']);
        
        // Scope Dosen list for examiners to the same prodi as the student
        $dosenList = Dosen::with('user')
            ->where('prodi_id', $ujian->mahasiswa->prodi_id)
            ->get();

        return view('admin.skripsi.pendaftaran-ujian-show', compact('ujian', 'dosenList'));
    }

    public function verifySyarat(Request $request, PendaftaranUjian $ujian, SyaratUjianUpload $syarat)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected',
            'catatan' => 'nullable|string',
        ]);

        $syarat->update([
            'status' => $validated['status'],
            'catatan' => $validated['catatan'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Persyaratan "' . $syarat->nama_persyaratan . '" berhasil diverifikasi.');
    }

    public function setJadwalUjian(Request $request, PendaftaranUjian $ujian)
    {
        $validated = $request->validate([
            'tanggal_ujian' => 'required|date',
            'jam_mulai' => 'required',
            'jam_selesai' => 'required',
            'ruangan' => 'required|string|max:100',
            'penguji1_id' => 'required|exists:dosen,id',
            'penguji2_id' => 'required|exists:dosen,id|different:penguji1_id',
        ]);

        $ujian->update($validated);

        return redirect()->back()->with('success', 'Jadwal dan Penguji ujian berhasil ditentukan.');
    }

    public function approvePendaftaran(Request $request, PendaftaranUjian $ujian)
    {
        // Check if all prerequisites are approved
        $unapprovedCount = $ujian->syaratUpload()->where('status', '!=', 'approved')->count();
        if ($unapprovedCount > 0) {
            return redirect()->back()->with('error', 'Semua berkas persyaratan harus disetujui (Approved) sebelum menyetujui pendaftaran ujian.');
        }

        if (!$ujian->tanggal_ujian || !$ujian->penguji1_id || !$ujian->penguji2_id) {
            return redirect()->back()->with('error', 'Harap tentukan jadwal ujian dan tim penguji terlebih dahulu.');
        }

        $ujian->update(['status' => 'approved']);

        // Update Skripsi milestone date and status
        $skripsi = $ujian->skripsi;
        $jenis = $ujian->jenis_ujian;

        if ($jenis === 'proposal') {
            $skripsi->update([
                'status' => Skripsi::STATUS_SEMINAR_PROPOSAL,
                'tanggal_seminar_proposal' => $ujian->tanggal_ujian
            ]);
        } elseif ($jenis === 'hasil') {
            $skripsi->update([
                'status' => Skripsi::STATUS_SEMINAR_HASIL,
                'tanggal_seminar_hasil' => $ujian->tanggal_ujian
            ]);
        } elseif ($jenis === 'sidang') {
            $skripsi->update([
                'status' => Skripsi::STATUS_SIDANG,
                'tanggal_sidang' => $ujian->tanggal_ujian
            ]);
        }

        return redirect()->back()->with('success', 'Pendaftaran ujian berhasil disetujui.');
    }

    public function rejectPendaftaran(Request $request, PendaftaranUjian $ujian)
    {
        $validated = $request->validate([
            'catatan' => 'required|string',
        ]);

        $ujian->update([
            'status' => 'rejected',
            'catatan' => $validated['catatan']
        ]);

        return redirect()->back()->with('success', 'Pendaftaran ujian berhasil ditolak.');
    }
}
