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
        // Hanya redirect ke pembayaran jika belum bayar (status success)
        if ($tahunAktif && !$this->paymentService->isPaid($mahasiswa, $tahunAktif)) {
            return redirect()->route('mahasiswa.pembayaran.index')
                ->with('error', 'Silakan lakukan upload bukti pembayaran dan tunggu verifikasi admin sebelum mengisi KRS.');
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

                // Tampilkan semua mata kuliah sesuai paritas semester (Ganjil/Genap)
                // Hapus matkul yang sudah pernah diambil di KRS sebelumnya
                $q->whereNotIn('mata_kuliah.id', $takenMkIds)
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

        $availableMataKuliah = \App\Models\MataKuliah::with(['kelas' => fn($q) => $q->where('tahun_akademik_id', $tahunAktif?->id)->with(['dosen.user', 'krsDetail'])])
            ->where(function($q) use ($mahasiswa, $takenMkIds, $krs, $tahunAktif, $targetSemester) {
                // Filter ganjil/genap berdasarkan semester tahun akademik aktif
                if ($tahunAktif) {
                    if (strtolower($tahunAktif->semester) === 'ganjil') {
                        $q->whereRaw('semester % 2 != 0');
                    } else {
                        $q->whereRaw('semester % 2 = 0');
                    }
                }

                // Tampilkan semua mata kuliah sesuai paritas semester (Ganjil/Genap)
                // Hapus matkul yang sudah pernah diambil di KRS sebelumnya
                $q->whereNotIn('mata_kuliah.id', $takenMkIds)
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
            ->whereDoesntHave('kelas.krsDetail', function($q) use ($krs) {
                $q->where('krs_id', $krs->id);
            })
            ->get()
            ->groupBy(fn($mk) => 'Semester ' . $mk->semester);
        
        // Sort by semester number
        $availableMataKuliah = $availableMataKuliah->sortKeys();

        // Concentration logic: Show if target semester >= 5
        $concentrations = collect();
        if ($targetSemester >= 5) {
            $concentrations = Konsentrasi::where('prodi_id', $mahasiswa->prodi_id)
                ->where('is_active', true)
                ->get();
        }

        return view('mahasiswa.krs.index', compact('krs', 'availableMataKuliah', 'concentrations'));
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
        $request->validate(['mata_kuliah_id' => 'required|exists:mata_kuliah,id']);
        
        $mahasiswa = Auth::user()->mahasiswa;
        $krs = $this->krsService->getActiveKrsOrNew($mahasiswa);
        $tahunAktif = \App\Models\TahunAkademik::where('is_active', true)->first();
        if (!$tahunAktif) {
            return redirect()->back()->with('error', 'Tidak ada tahun akademik aktif.');
        }

        // Cari atau buat Kelas untuk mata kuliah ini di tahun akademik aktif
        $kelas = \App\Models\Kelas::where('mata_kuliah_id', $request->mata_kuliah_id)
            ->where('tahun_akademik_id', $tahunAktif->id)
            ->first();

        if (!$kelas) {
            $defaultDosen = \App\Models\Dosen::where('prodi_id', $mahasiswa->prodi_id)->first() ?? \App\Models\Dosen::first();
            if (!$defaultDosen) {
                return redirect()->back()->with('error', 'Dosen pengampu default tidak ditemukan.');
            }
            $kelas = \App\Models\Kelas::create([
                'mata_kuliah_id' => $request->mata_kuliah_id,
                'dosen_id' => $defaultDosen->id,
                'tahun_akademik_id' => $tahunAktif->id,
                'nama_kelas' => 'Kelas A',
                'kapasitas' => 40,
                'is_closed' => false,
            ]);
        }

        try {
            $this->krsService->addKelas($krs, $kelas->id);
            return redirect()->back()->with('success', 'Mata kuliah berhasil diambil');
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
