<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SyaratUjianUpload extends Model
{
    use HasFactory;

    protected $table = 'syarat_ujian_upload';

    protected $fillable = [
        'pendaftaran_ujian_id',
        'nama_persyaratan',
        'file_path',
        'status',
        'catatan',
    ];

    public function pendaftaranUjian()
    {
        return $this->belongsTo(PendaftaranUjian::class, 'pendaftaran_ujian_id');
    }
}
