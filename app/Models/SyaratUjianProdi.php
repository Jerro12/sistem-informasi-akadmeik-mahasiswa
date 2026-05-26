<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SyaratUjianProdi extends Model
{
    protected $table = 'syarat_ujian_prodi';

    protected $fillable = [
        'prodi_id',
        'jenis_ujian',
        'nama_persyaratan',
        'file_name_key',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }
}
