<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaKuliah extends Model
{
    use HasFactory;

    protected $table = 'biaya_kuliah';

    protected $fillable = [
        'tahun_akademik_id',
        'prodi_id',
        'nominal',
    ];

    public function tahunAkademik()
    {
        return $this->belongsTo(TahunAkademik::class);
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class, 'prodi_id');
    }
}
