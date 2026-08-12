<?php

use App\Models\Dosen;
use App\Models\Fakultas;
use App\Models\JadwalKuliah;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Materi;
use App\Models\Pertemuan;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;
use App\Services\PresensiService;
use App\Services\AkademikCalculationService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->tahunAkademik = TahunAkademik::create([
        'tahun' => '2025/2026',
        'semester' => 'ganjil',
        'is_active' => true,
    ]);
    
    $this->fakultas = Fakultas::create([
        'kode' => 'FT',
        'nama' => 'Teknik',
    ]);

    $this->prodi = Prodi::create([
        'fakultas_id' => $this->fakultas->id,
        'kode' => 'IF',
        'nama' => 'Informatika',
        'jenjang' => 'S1',
    ]);
    
    $this->dosenUser = User::factory()->create(['role' => 'dosen']);
    $this->dosen = Dosen::create([
        'user_id' => $this->dosenUser->id,
        'prodi_id' => $this->prodi->id,
        'nidn' => '1234567890',
        'no_hp' => '081234567890',
        'jenis_kelamin' => 'L',
        'tempat_lahir' => 'Parepare',
        'tanggal_lahir' => '1985-01-01',
        'alamat' => 'Jl. Pendidikan No. 1',
    ]);

    $this->mk = MataKuliah::create([
        'prodi_id' => $this->prodi->id,
        'kode_mk' => 'IF101',
        'nama_mk' => 'Algoritma & Pemrograman',
        'sks' => 3,
        'semester' => 1,
    ]);

    $this->kelas = Kelas::create([
        'mata_kuliah_id' => $this->mk->id,
        'dosen_id' => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
        'nama_kelas' => 'A',
        'kapasitas' => 40,
    ]);
});

test('presensi service returns all enrolled students in krsDetail', function () {
    for ($i = 0; $i < 4; $i++) {
        $mhsUser = User::factory()->create(['role' => 'mahasiswa']);
        $mhs = Mahasiswa::create([
            'user_id' => $mhsUser->id,
            'prodi_id' => $this->prodi->id,
            'nim' => '202600' . ($i + 1),
            'angkatan' => 2025,
        ]);
        $krs = Krs::create([
            'mahasiswa_id' => $mhs->id,
            'tahun_akademik_id' => $this->tahunAkademik->id,
            'status' => $i < 2 ? 'approved' : 'pending',
        ]);
        KrsDetail::create([
            'krs_id' => $krs->id,
            'kelas_id' => $this->kelas->id,
        ]);
    }

    $presensiService = app(PresensiService::class);
    $rekap = $presensiService->getPresensiByKelas($this->kelas->id);
    
    expect($rekap->count())->toBe(4);
});

test('dosen can download materi gracefully', function () {
    Storage::fake('public');
    
    $jadwal = JadwalKuliah::create([
        'kelas_id' => $this->kelas->id,
        'dosen_id' => $this->dosen->id,
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:30',
        'ruangan' => 'Lab 1',
    ]);

    $pertemuan = Pertemuan::create([
        'jadwal_kuliah_id' => $jadwal->id,
        'pertemuan_ke' => 1,
        'tanggal' => now()->toDateString(),
    ]);

    Storage::disk('public')->put("materi/kelas_{$this->kelas->id}/sample.pdf", 'dummy content');

    $materi = Materi::create([
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Sample Materi',
        'file_path' => "materi/kelas_{$this->kelas->id}/sample.pdf",
        'file_name' => 'sample.pdf',
    ]);

    $response = $this->actingAs($this->dosenUser)
        ->get(route('dosen.materi.download', [$this->kelas->id, $materi->id]));

    $response->assertStatus(200);
});

test('dosen download missing file returns redirect error instead of 404 crash', function () {
    $jadwal = JadwalKuliah::create([
        'kelas_id' => $this->kelas->id,
        'dosen_id' => $this->dosen->id,
        'hari' => 'Senin',
        'jam_mulai' => '08:00',
        'jam_selesai' => '10:30',
        'ruangan' => 'Lab 1',
    ]);

    $pertemuan = Pertemuan::create([
        'jadwal_kuliah_id' => $jadwal->id,
        'pertemuan_ke' => 1,
        'tanggal' => now()->toDateString(),
    ]);

    $materi = Materi::create([
        'pertemuan_id' => $pertemuan->id,
        'judul' => 'Missing Materi',
        'file_path' => 'materi/non_existent.pdf',
        'file_name' => 'non_existent.pdf',
    ]);

    $response = $this->actingAs($this->dosenUser)
        ->get(route('dosen.materi.download', [$this->kelas->id, $materi->id]));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

test('akademik calculation service getBobot handles standard A B C D E grade scale correctly', function () {
    $service = app(AkademikCalculationService::class);
    
    expect($service->getBobot('A'))->toBe(4.0);
    expect($service->getBobot('B'))->toBe(3.0);
    expect($service->getBobot('C'))->toBe(2.0);
    expect($service->getBobot('D'))->toBe(1.0);
    expect($service->getBobot('E'))->toBe(0.0);
});
