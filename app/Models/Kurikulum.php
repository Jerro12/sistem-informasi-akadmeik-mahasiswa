<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kurikulum extends Model
{
    use HasFactory;

    protected $table = 'kurikulum';

    protected $fillable = [
        'nama',
        'prodi_id',
        'tahun_mulai',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function mataKuliah()
    {
        return $this->hasMany(MataKuliah::class, 'kurikulum_id');
    }

    public function mahasiswa()
    {
        return $this->hasMany(Mahasiswa::class, 'kurikulum_id');
    }
}
