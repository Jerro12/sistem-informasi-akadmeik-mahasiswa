<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAkademik;
use App\Models\MataKuliah;
use App\Models\Kelas;
use App\Models\Dosen;

class TIClassesSeeder extends Seeder
{
    public function run(): void
    {
        $tahunAktif = TahunAkademik::where('is_active', true)->first();
        if (!$tahunAktif) {
            $this->command->error("Tahun Akademik Aktif tidak ditemukan!");
            return;
        }

        $dosen = Dosen::first();
        if (!$dosen) {
            $this->command->error("Dosen tidak ditemukan untuk mengajar kelas!");
            return;
        }

        // Ambil semua matkul TI (prodi_id = 1) untuk Semester 6 & 8
        $mks = MataKuliah::where('prodi_id', 1)
            ->whereIn('semester', [6, 8])
            ->get();

        $this->command->info("Memproses " . $mks->count() . " mata kuliah Semester 6 & 8 Teknik Informatika...");

        $createdCount = 0;
        foreach ($mks as $mk) {
            // firstOrCreate mencegah terjadinya duplikasi data jika seeder dijalankan berulang kali
            $kelas = Kelas::firstOrCreate([
                'mata_kuliah_id' => $mk->id,
                'tahun_akademik_id' => $tahunAktif->id,
            ], [
                'dosen_id' => $dosen->id,
                'nama_kelas' => 'A',
                'kapasitas' => 40,
                'is_closed' => false,
            ]);

            if ($kelas->wasRecentlyCreated) {
                $this->command->info("- Kelas berhasil dibuat: {$mk->nama_mk} (Smt {$mk->semester})");
                $createdCount++;
            } else {
                $this->command->warn("- Kelas sudah ada: {$mk->nama_mk} (Smt {$mk->semester})");
            }
        }

        $this->command->info("Selesai! Berhasil menambahkan {$createdCount} kelas baru tanpa menghapus data lama.");
    }
}
