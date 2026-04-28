<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\KrsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class KrsController extends Controller
{
    protected $krsService;
    protected $paymentService;

    public function __construct(KrsService $krsService, \App\Services\PaymentService $paymentService)
    {
        $this->krsService = $krsService;
        $this->paymentService = $paymentService;
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) abort(403, 'Unauthorized');

        // Check for payment
        $tahunAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
        if ($tahunAktif && !$this->paymentService->isPaid($mahasiswa, $tahunAktif)) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Silakan lakukan pembayaran biaya KRS terlebih dahulu untuk semester ini.');
        }

        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);
        
        // Ambil ID mata kuliah yang sudah pernah diambil mahasiswa di semester-semester sebelumnya
        $takenMkIds = \App\Models\KrsDetail::whereHas('krs', function($q) use ($mahasiswa) {
            $q->where('mahasiswa_id', $mahasiswa->id);
        })->get()->pluck('kelas.mata_kuliah_id')->unique()->toArray();

        $availableKelas = \App\Models\Kelas::with(['mataKuliah', 'dosen.user', 'krsDetail'])
            ->where('tahun_akademik_id', $tahunAktif?->id) 
            ->whereHas('mataKuliah', function($q) use ($mahasiswa, $takenMkIds) {
                // Tampilkan matkul semester sekarang (Wajib tampil)
                // ATAU matkul semester bawah (<) yang BELUM PERNAH diambil
                $q->where(function($query) use ($mahasiswa, $takenMkIds) {
                    $query->where('semester', $mahasiswa->semester_sekarang)
                          ->orWhere(function($q2) use ($mahasiswa, $takenMkIds) {
                              $q2->where('semester', '<', $mahasiswa->semester_sekarang)
                                 ->whereNotIn('mata_kuliah.id', $takenMkIds);
                          });
                })
                ->where(function($query) use ($mahasiswa) {
                    $query->where('prodi_id', $mahasiswa->prodi_id)
                          ->orWhereNull('prodi_id');
                });
                // Kita buat filter kurikulum opsional: jika mahasiswa punya kurikulum, filter. 
                // Jika tidak punya, tampilkan semua matkul prodi tersebut.
                if ($mahasiswa->kurikulum_id) {
                    $q->where(function($query) use ($mahasiswa) {
                        $query->where('kurikulum_id', $mahasiswa->kurikulum_id)
                              ->orWhereNull('kurikulum_id');
                    });
                }
            })
            ->whereDoesntHave('krsDetail', function($q) use ($krs) {
                $q->where('krs_id', $krs->id);
            })
            ->get()
            ->groupBy(fn($k) => 'Semester ' . $k->mataKuliah->semester);
        
        // Sort by semester number
        $availableKelas = $availableKelas->sortKeys();

        return view('mahasiswa.krs.index', compact('krs', 'availableKelas'));
    }

    public function store(Request $request)
    {
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);
        
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        try {
            $this->krsService->addKelas($krs, $request->kelas_id);
            return redirect()->back()->with('success', 'Kelas berhasil diambil');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function destroy($detailId)
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        try {
            $this->krsService->removeKelas($krs, $detailId);
            return redirect()->back()->with('success', 'Kelas dibatalkan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function submit()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        try {
            $this->krsService->submitKrs($krs);
            return redirect()->back()->with('success', 'KRS berhasil diajukan');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function revise()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        if ($krs->status !== 'rejected') {
            return redirect()->back()->with('error', 'Hanya KRS yang ditolak yang dapat direvisi');
        }

        $krs->update(['status' => 'draft', 'catatan' => null]);
        return redirect()->back()->with('success', 'KRS berhasil direset ke draft. Silakan edit dan ajukan kembali.');
    }
    public function finalize()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        if ($krs->krsDetail()->count() === 0) {
            return redirect()->back()->with('error', 'Pilih minimal satu mata kuliah sebelum mematenkan KRS.');
        }

        $krs->update(['status' => 'approved']);
        return redirect()->back()->with('success', 'KRS Berhasil dipatenkan dan dikunci. Silakan cetak KRS Anda.');
    }
}
