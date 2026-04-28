<?php

namespace App\Services;

use App\Models\Pembayaran;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class PaymentService
{
    public function __construct()
    {
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Create a payment record and return Snap Token
     */
    public function createPayment(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik, $amount)
    {
        // Check if there is already a pending payment for this student and semester
        $existing = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('status', 'pending')
            ->first();

        if ($existing && $existing->snap_token) {
            return $existing->snap_token;
        }

        $orderId = 'PAY-' . time() . '-' . $mahasiswa->id;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => [
                'first_name' => $mahasiswa->user->name,
                'email' => $mahasiswa->user->email,
            ],
            'item_details' => [
                [
                    'id' => 'KRS-' . $tahunAkademik->id,
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => 'Biaya Kuliah/KRS ' . $tahunAkademik->tahun . ' ' . $tahunAkademik->semester,
                ]
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            Pembayaran::create([
                'mahasiswa_id' => $mahasiswa->id,
                'tahun_akademik_id' => $tahunAkademik->id,
                'order_id' => $orderId,
                'amount' => $amount,
                'status' => 'pending',
                'snap_token' => $snapToken,
            ]);

            return $snapToken;
        } catch (Exception $e) {
            throw new Exception('Gagal membuat transaksi ke Midtrans: ' . $e->getMessage());
        }
    }

    /**
     * Verify if student has paid for the current semester
     */
    public function isPaid(Mahasiswa $mahasiswa, TahunAkademik $tahunAkademik): bool
    {
        return Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('tahun_akademik_id', $tahunAkademik->id)
            ->where('status', 'success')
            ->exists();
    }
    /**
     * Get transaction status from Midtrans
     */
    public function getStatus($orderId)
    {
        try {
            return \Midtrans\Transaction::status($orderId);
        } catch (Exception $e) {
            return null;
        }
    }
}
