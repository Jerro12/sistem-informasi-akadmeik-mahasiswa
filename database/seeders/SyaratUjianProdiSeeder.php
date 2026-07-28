<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Prodi;
use App\Models\SyaratUjianProdi;
use Illuminate\Support\Str;

class SyaratUjianProdiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $prodis = Prodi::all();

        $syaratProposal = [
            'Transkrip Nilai (Telah Melulusi Minimal 110 SKS)',
            'SK Pembimbing',
            'Bukti Pembayaran (Screenshot E-Cash Telah Bayar)',
            'Sertifikat Lab TI',
            'Persetujuan Pembimbing',
            'Surat Berhak Mengikuti Ujian (Fakultas)',
            'Krs Semester Berjalan',
            'Kartu Monitoring Bimbingan',
        ];

        $syaratHasil = [
            'Semua Berkas Pendaftaran Ujian Proposal',
            'Keterangan Uji Plagiasi/Turnitin',
        ];

        $syaratSidang = [
            'Transkrip Nilai (Telah Melulusi 140 SKS)',
            'Foto Copy SK Pembimbing Skripsi',
            'Foto Copy Persetujuan Pembimbing',
            'Foto Copy Pelunasan Pembayaran (Biro Keuangan)',
            'Foto Copy Sertifikat UKOM AIK',
            'Lulus Plagiasi (<25%)',
            'KRS Berjalan',
            'Foto Copy Kartu Monitoring',
        ];

        foreach ($prodis as $prodi) {
            // Delete old seed data if needed to avoid conflicts
            // Proposal
            foreach ($syaratProposal as $nama) {
                SyaratUjianProdi::firstOrCreate([
                    'prodi_id' => $prodi->id,
                    'jenis_ujian' => 'proposal',
                    'nama_persyaratan' => $nama,
                ], [
                    'file_name_key' => 'prop_' . Str::slug($nama, '_'),
                    'is_required' => true,
                ]);
            }

            // Hasil
            foreach ($syaratHasil as $nama) {
                SyaratUjianProdi::firstOrCreate([
                    'prodi_id' => $prodi->id,
                    'jenis_ujian' => 'hasil',
                    'nama_persyaratan' => $nama,
                ], [
                    'file_name_key' => 'hasil_' . Str::slug($nama, '_'),
                    'is_required' => true,
                ]);
            }

            // Sidang / Ujian Tutup
            foreach ($syaratSidang as $nama) {
                SyaratUjianProdi::firstOrCreate([
                    'prodi_id' => $prodi->id,
                    'jenis_ujian' => 'sidang',
                    'nama_persyaratan' => $nama,
                ], [
                    'file_name_key' => 'sidang_' . Str::slug($nama, '_'),
                    'is_required' => true,
                ]);
            }
        }
    }
}

