<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayaran';

    protected $fillable = [
        'mahasiswa_id',
        'tahun_akademik_id',
        'order_id',
        'amount',
        'status',
        'snap_token',
        'payment_type',
        'transaction_time',
        'settlement_time',
        'bukti_transfer',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }
}
