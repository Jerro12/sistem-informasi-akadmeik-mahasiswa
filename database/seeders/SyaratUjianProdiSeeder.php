<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SyaratUjianProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodis = \App\Models\Prodi::all();
        foreach ($prodis as $prodi) {
            // Proposal
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'proposal',
                'nama_persyaratan' => 'KRS Aktif',
                'file_name_key' => 'file_krs',
            ]);
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'proposal',
                'nama_persyaratan' => 'Transkrip Nilai Sementara',
                'file_name_key' => 'file_transkrip',
            ]);
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'proposal',
                'nama_persyaratan' => 'Draf Proposal Skripsi',
                'file_name_key' => 'file_draft_proposal',
            ]);

            // Hasil
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'hasil',
                'nama_persyaratan' => 'Naskah Hasil Penelitian',
                'file_name_key' => 'file_naskah_hasil',
            ]);
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'hasil',
                'nama_persyaratan' => 'Logbook Bimbingan',
                'file_name_key' => 'file_logbook',
            ]);

            // Sidang
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'sidang',
                'nama_persyaratan' => 'Naskah Skripsi Lengkap',
                'file_name_key' => 'file_naskah_lengkap',
            ]);
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'sidang',
                'nama_persyaratan' => 'Surat Bebas Pustaka',
                'file_name_key' => 'file_bebas_pustaka',
            ]);
            \App\Models\SyaratUjianProdi::firstOrCreate([
                'prodi_id' => $prodi->id,
                'jenis_ujian' => 'sidang',
                'nama_persyaratan' => 'Bukti Pembayaran / Bebas Tunggakan',
                'file_name_key' => 'file_bukti_bebas_tunggakan',
            ]);
        }
    }
}
