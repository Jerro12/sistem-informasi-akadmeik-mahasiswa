<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mahasiswa extends Model
{
    use HasFactory;

    protected $table = 'mahasiswa';

    protected $fillable = [
        'user_id',
        'nim',
        'prodi_id',
        'dosen_pa_id',
        'angkatan',
        'semester_sekarang',
        'status',
        'kurikulum_id',
        'no_hp',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'provinsi',
        'kecamatan',
        'kelurahan',
        'desa',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function kurikulum()
    {
        return $this->belongsTo(Kurikulum::class);
    }

    public function dosenPa()
    {
        return $this->belongsTo(Dosen::class, 'dosen_pa_id');
    }

    public function krs()
    {
        return $this->hasMany(Krs::class);
    }

    public function nilai()
    {
        return $this->hasMany(Nilai::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'aktif';
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class);
    }

    public function tugasSubmissions()
    {
        return $this->hasMany(TugasSubmission::class);
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function getIpkAttribute()
    {
        $calc = app(\App\Services\AkademikCalculationService::class)->calculateIPK($this);
        return $calc['ipk'];
    }
}
