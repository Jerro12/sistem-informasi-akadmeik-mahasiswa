<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\KrsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\Konsentrasi;

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
        // Hanya redirect ke pembayaran jika biaya_krs > 0 dan belum bayar
        if ($tahunAktif && $tahunAktif->biaya_krs > 0 && !$this->paymentService->isPaid($mahasiswa, $tahunAktif)) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Silakan lakukan pembayaran biaya KRS terlebih dahulu untuk semester ini.');
        }

        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);
        
        // Hitung target semester secara dinamis agar jika paritas semester mhs vs TA mismatch
        // (misal: mhs masih terdaftar semester ganjil tapi TA sudah ganti genap), mhs otomatis melihat matkul semester depannya.
        $targetSemester = $mahasiswa->semester_sekarang;
        if ($tahunAktif) {
            $isStudentSemesterEven = ($mahasiswa->semester_sekarang % 2 === 0);
            $isTaSemesterEven = (strtolower($tahunAktif->semester) === 'genap');
            if ($isStudentSemesterEven !== $isTaSemesterEven) {
                $targetSemester = $mahasiswa->semester_sekarang + 1;
            }
        }
        
        // Ambil ID mata kuliah yang sudah pernah diambil mahasiswa di KRS SEBELUMNYA
        // (exclude KRS semester aktif saat ini, agar matkul yang dihapus dari draft bisa ditambah lagi)
        $takenMkIds = \App\Models\KrsDetail::whereHas('krs', function($q) use ($mahasiswa, $krs) {
            $q->where('mahasiswa_id', $mahasiswa->id)
              ->where('id', '!=', $krs->id); // Exclude KRS aktif saat ini
        })->get()->pluck('kelas.mata_kuliah_id')->unique()->filter()->toArray();

        // Auto-create Kelas for eligible MataKuliah if not exists
        if ($tahunAktif) {
            $matchingMataKuliahs = \App\Models\MataKuliah::where(function($q) use ($mahasiswa, $takenMkIds, $krs, $tahunAktif, $targetSemester) {
                // Filter ganjil/genap berdasarkan semester tahun akademik aktif
                if (strtolower($tahunAktif->semester) === 'ganjil') {
                    $q->whereRaw('semester % 2 != 0');
                } else {
                    $q->whereRaw('semester % 2 = 0');
                }

                // Tampilkan matkul semester target (Wajib tampil)
                // ATAU matkul semester bawah (<) yang BELUM PERNAH diambil di KRS sebelumnya
                $q->where(function($query) use ($mahasiswa, $takenMkIds, $targetSemester) {
                    $query->where('semester', $targetSemester)
                          ->orWhere(function($q2) use ($mahasiswa, $takenMkIds, $targetSemester) {
                              $q2->where('semester', '<', $targetSemester)
                                 ->whereNotIn('mata_kuliah.id', $takenMkIds);
                          });
                })
                ->where(function($query) use ($mahasiswa) {
                    $query->where('prodi_id', $mahasiswa->prodi_id)
                          ->orWhereNull('prodi_id');
                });

                if ($mahasiswa->kurikulum_id) {
                    $q->where(function($query) use ($mahasiswa) {
                        $query->where('kurikulum_id', $mahasiswa->kurikulum_id)
                              ->orWhereNull('kurikulum_id');
                    });
                }
                
                if ($krs->konsentrasi_id) {
                    $q->where(function($query) use ($krs) {
                        $query->where('konsentrasi_id', $krs->konsentrasi_id)
                              ->orWhereNull('konsentrasi_id');
                    });
                } else {
                    $q->whereNull('konsentrasi_id');
                }
            })->get();

            $defaultDosen = \App\Models\Dosen::where('prodi_id', $mahasiswa->prodi_id)->first() ?? \App\Models\Dosen::first();

            foreach ($matchingMataKuliahs as $mk) {
                $exists = \App\Models\Kelas::where('mata_kuliah_id', $mk->id)
                    ->where('tahun_akademik_id', $tahunAktif->id)
                    ->exists();
                if (!$exists && $defaultDosen) {
                    \App\Models\Kelas::create([
                        'mata_kuliah_id' => $mk->id,
                        'dosen_id' => $defaultDosen->id,
                        'tahun_akademik_id' => $tahunAktif->id,
                        'nama_kelas' => 'Kelas A',
                        'kapasitas' => 40,
                        'is_closed' => false,
                    ]);
                }
            }
        }

        $availableKelas = \App\Models\Kelas::with(['mataKuliah', 'dosen.user', 'krsDetail'])
            ->where('tahun_akademik_id', $tahunAktif?->id)
            ->where('is_closed', false) // [FIX] Jangan tampilkan kelas yang sudah ditutup
            ->whereHas('mataKuliah', function($q) use ($mahasiswa, $takenMkIds, $krs, $tahunAktif, $targetSemester) {
                // Filter ganjil/genap berdasarkan semester tahun akademik aktif
                if ($tahunAktif) {
                    if (strtolower($tahunAktif->semester) === 'ganjil') {
                        $q->whereRaw('semester % 2 != 0');
                    } else {
                        $q->whereRaw('semester % 2 = 0');
                    }
                }

                // Tampilkan matkul semester target (Wajib tampil)
                // ATAU matkul semester bawah (<) yang BELUM PERNAH diambil di KRS sebelumnya
                $q->where(function($query) use ($mahasiswa, $takenMkIds, $targetSemester) {
                    $query->where('semester', $targetSemester)
                          ->orWhere(function($q2) use ($mahasiswa, $takenMkIds, $targetSemester) {
                              $q2->where('semester', '<', $targetSemester)
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
                
                // Filter Konsentrasi: Tampilkan matkul yang konsentrasinya cocok dengan yang dipilih di KRS,
                // atau matkul umum (konsentrasi_id null)
                if ($krs->konsentrasi_id) {
                    $q->where(function($query) use ($krs) {
                        $query->where('konsentrasi_id', $krs->konsentrasi_id)
                              ->orWhereNull('konsentrasi_id');
                    });
                } else {
                    $q->whereNull('konsentrasi_id');
                }
            })
            ->whereDoesntHave('krsDetail', function($q) use ($krs) {
                $q->where('krs_id', $krs->id);
            })
            ->get()
            ->groupBy(fn($k) => 'Semester ' . $k->mataKuliah->semester);
        
        // Sort by semester number
        $availableKelas = $availableKelas->sortKeys();

        // Concentration logic: Show if target semester >= 5
        $concentrations = collect();
        if ($targetSemester >= 5) {
            $concentrations = Konsentrasi::where('prodi_id', $mahasiswa->prodi_id)
                ->where('is_active', true)
                ->get();
        }

        return view('mahasiswa.krs.index', compact('krs', 'availableKelas', 'concentrations'));
    }

    public function updateConcentration(Request $request)
    {
        $request->validate(['konsentrasi_id' => 'required|exists:konsentrasi,id']);
        
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);

        $krs->update(['konsentrasi_id' => $request->konsentrasi_id]);

        return redirect()->back()->with('success', 'Konsentrasi berhasil dipilih');
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

        $krs->update(['status' => 'pending']);
        return redirect()->back()->with('success', 'KRS Berhasil dipatenkan. Silakan tunggu verifikasi admin/dosen untuk mencetak.');
    }
}
