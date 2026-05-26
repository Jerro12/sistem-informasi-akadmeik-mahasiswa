<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendaftaranUjian extends Model
{
    use HasFactory;

    protected $table = 'pendaftaran_ujian';

    protected $fillable = [
        'mahasiswa_id',
        'skripsi_id',
        'jenis_ujian',
        'tanggal_ujian',
        'jam_mulai',
        'jam_selesai',
        'ruangan',
        'penguji1_id',
        'penguji2_id',
        'status',
        'catatan',
    ];

    protected $casts = [
        'tanggal_ujian' => 'date',
    ];

    public function mahasiswa()
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function skripsi()
    {
        return $this->belongsTo(Skripsi::class);
    }

    public function penguji1()
    {
        return $this->belongsTo(Dosen::class, 'penguji1_id');
    }

    public function penguji2()
    {
        return $this->belongsTo(Dosen::class, 'penguji2_id');
    }

    public function syaratUpload()
    {
        return $this->hasMany(SyaratUjianUpload::class, 'pendaftaran_ujian_id');
    }
}
