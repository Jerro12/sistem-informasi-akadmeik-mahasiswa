<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use App\Services\AkademikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    protected $paymentService;
    protected $akademikService;

    public function __construct(PaymentService $paymentService, AkademikService $akademikService)
    {
        $this->paymentService = $paymentService;
        $this->akademikService = $akademikService;
    }

    public function index()
    {
        $mahasiswa = Auth::user()->mahasiswa;
        $tahunAktif = $this->akademikService->getActiveTahun();

        if (!$tahunAktif) {
            return view('mahasiswa.payment.index')->with('error', 'Tidak ada semester aktif');
        }

        $pembayaran = \App\Models\Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAktif->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $isPaid = $this->paymentService->isPaid($mahasiswa, $tahunAktif);
        
        // Get amount from BiayaKuliah (Prodi specific), fallback to TahunAkademik, then config
        $biayaProdi = \App\Models\BiayaKuliah::where('tahun_akademik_id', $tahunAktif->id)
            ->where('prodi_id', $mahasiswa->prodi_id)
            ->first();
            
        $biayaKrs = $biayaProdi ? $biayaProdi->nominal : ($tahunAktif->biaya_krs > 0 ? $tahunAktif->biaya_krs : config('siakad.biaya_krs'));

        // Check for pending payment to get/refresh snap token
        $snapToken = null;
        $pendingPayment = $pembayaran->where('status', 'pending')->first();
        
        if ($pendingPayment) {
            // Check real status from Midtrans (Polling fallback for localhost)
            $midtransStatus = $this->paymentService->getStatus($pendingPayment->order_id);
            
            if ($midtransStatus) {
                $status = $midtransStatus->transaction_status;
                if (in_array($status, ['settlement', 'capture'])) {
                    $pendingPayment->update([
                        'status' => 'success',
                        'payment_type' => $midtransStatus->payment_type ?? $pendingPayment->payment_type
                    ]);
                    $isPaid = true;
                    $pendingPayment = null; // No longer pending
                } elseif (in_array($status, ['deny', 'expire', 'cancel'])) {
                    $pendingPayment->update(['status' => 'failed']);
                    $pendingPayment = null;
                }
            }
        }

        if ($pendingPayment) {
            $snapToken = $pendingPayment->snap_token;
        } elseif (!$isPaid) {
            try {
                $snapToken = $this->paymentService->createPayment($mahasiswa, $tahunAktif, $biayaKrs);
            } catch (\Exception $e) {
                // Log or handle error
            }
        }

        return view('mahasiswa.payment.index', compact('pembayaran', 'isPaid', 'biayaKrs', 'tahunAktif', 'snapToken'));
    }

    public function webhook(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $pembayaran = \App\Models\Pembayaran::where('order_id', $request->order_id)->first();
        if (!$pembayaran) {
            return response()->json(['message' => 'Transaction not found'], 404);
        }

        $status = $request->transaction_status;
        if ($status == 'settlement' || $status == 'capture') {
            $pembayaran->update([
                'status' => 'success',
                'payment_type' => $request->payment_type,
                'transaction_time' => $request->transaction_time,
                'settlement_time' => $request->settlement_time,
            ]);
        } elseif ($status == 'pending') {
            $pembayaran->update(['status' => 'pending']);
        } elseif ($status == 'deny' || $status == 'expire' || $status == 'cancel') {
            $pembayaran->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Success']);
    }
}
