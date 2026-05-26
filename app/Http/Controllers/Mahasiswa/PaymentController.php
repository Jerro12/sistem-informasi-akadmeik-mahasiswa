<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\AkademikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $akademikService;

    public function __construct(AkademikService $akademikService)
    {
        $this->akademikService = $akademikService;
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $tahunAktif = $this->akademikService->getActiveTahun();

        if (!$tahunAktif) {
            return view('mahasiswa.payment.index')->with('error', 'Tidak ada semester aktif');
        }

        $pembayaran = \App\Models\Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAktif->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $successPayment = $pembayaran->where('status', 'success')->first();
        $isPaid = !is_null($successPayment);

        $pendingPayment = $pembayaran->where('status', 'pending')->first();

        // Get amount from BiayaKuliah (Prodi specific), fallback to TahunAkademik, then config
        $biayaProdi = \App\Models\BiayaKuliah::where('tahun_akademik_id', $tahunAktif->id)
            ->where('prodi_id', $mahasiswa->prodi_id)
            ->first();
            
        $biayaKrs = $biayaProdi ? $biayaProdi->nominal : ($tahunAktif->biaya_krs > 0 ? $tahunAktif->biaya_krs : config('siakad.biaya_krs'));

        return view('mahasiswa.payment.index', compact('pembayaran', 'isPaid', 'biayaKrs', 'tahunAktif', 'pendingPayment'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'bukti_transfer' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $mahasiswa = Auth::user()->mahasiswa;
        if (!$mahasiswa) {
            abort(403, 'Unauthorized');
        }

        $tahunAktif = $this->akademikService->getActiveTahun();

        if (!$tahunAktif) {
            return redirect()->back()->with('error', 'Tidak ada semester aktif');
        }

        // Get amount
        $biayaProdi = \App\Models\BiayaKuliah::where('tahun_akademik_id', $tahunAktif->id)
            ->where('prodi_id', $mahasiswa->prodi_id)
            ->first();
            
        $biayaKrs = $biayaProdi ? $biayaProdi->nominal : ($tahunAktif->biaya_krs > 0 ? $tahunAktif->biaya_krs : config('siakad.biaya_krs'));

        // Handle file upload
        if ($request->hasFile('bukti_transfer')) {
            $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            
            \App\Models\Pembayaran::create([
                'mahasiswa_id' => $mahasiswa->id,
                'tahun_akademik_id' => $tahunAktif->id,
                'order_id' => 'PAY-' . time() . '-' . rand(100, 999),
                'amount' => $biayaKrs,
                'status' => 'pending',
                'bukti_transfer' => $path,
            ]);

            return redirect()->back()->with('success', 'Bukti transfer berhasil diunggah. Menunggu verifikasi dari admin.');
        }

        return redirect()->back()->with('error', 'Gagal mengunggah bukti transfer.');
    }
}
