<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    use HasFactory;

    protected $table = 'fakultas';

    protected $fillable = [
        'nama',
        'nama_dekan',
        'nama_wakil_dekan1',
    ];

    public function prodi()
    {
        return $this->hasMany(Prodi::class);
    }

    public function ruangan()
    {
        return $this->hasMany(Ruangan::class);
    }
}
