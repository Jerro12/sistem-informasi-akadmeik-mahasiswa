<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'superadmin']);
    $this->fakultas = Fakultas::factory()->create();
    $this->prodi = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);

    $dosenUser = User::factory()->create(['role' => 'dosen']);
    $this->dosen = Dosen::factory()->create([
        'user_id'     => $dosenUser->id,
        'prodi_id'    => $this->prodi->id,
        'fakultas_id' => $this->fakultas->id,
    ]);

    $this->tahunAkademik = TahunAkademik::factory()->create(['is_active' => true]);

    $this->mk = MataKuliah::create([
        'kode_mk'  => 'TI101',
        'nama_mk'  => 'Algoritma',
        'sks'      => 3,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);
});

// ========== KELAS CRUD ==========

test('admin dapat melihat daftar kelas', function () {
    Kelas::create([
        'mata_kuliah_id'   => $this->mk->id,
        'dosen_id'         => $this->dosen->id,
        'tahun_akademik_id'=> $this->tahunAkademik->id,
        'nama_kelas'       => 'Kelas A',
        'kapasitas'        => 40,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.kelas.index'));

    $response->assertStatus(200);
});

test('admin dapat membuat kelas baru', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
        'mata_kuliah_id' => $this->mk->id,
        'dosen_id'       => $this->dosen->id,
        'nama_kelas'     => 'Kelas B',
        'kapasitas'      => 35,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('kelas', [
        'nama_kelas'     => 'Kelas B',
        'kapasitas'      => 35,
        'mata_kuliah_id' => $this->mk->id,
    ]);
});

test('admin dapat membuat kelas dengan jadwal', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
        'mata_kuliah_id' => $this->mk->id,
        'dosen_id'       => $this->dosen->id,
        'nama_kelas'     => 'Kelas C',
        'kapasitas'      => 30,
        'hari'           => 'Senin',
        'jam_mulai'      => '08:00',
        'jam_selesai'    => '10:00',
        'ruangan'        => 'Ruang 101',
    ]);

    $response->assertRedirect();
    $kelas = Kelas::where('nama_kelas', 'Kelas C')->first();
    $this->assertNotNull($kelas);
    $this->assertDatabaseHas('jadwal_kuliah', [
        'kelas_id'   => $kelas->id,
        'hari'       => 'Senin',
        'ruangan'    => 'Ruang 101',
    ]);
});

test('admin dapat membuat kelas dengan jadwal hari minggu', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
        'mata_kuliah_id' => $this->mk->id,
        'dosen_id'       => $this->dosen->id,
        'nama_kelas'     => 'Kelas Minggu',
        'kapasitas'      => 30,
        'hari'           => 'Minggu',
        'jam_mulai'      => '09:00',
        'jam_selesai'    => '11:00',
        'ruangan'        => 'Ruang 202',
    ]);

    $response->assertRedirect();
    $kelas = Kelas::where('nama_kelas', 'Kelas Minggu')->first();
    $this->assertNotNull($kelas);
    $this->assertDatabaseHas('jadwal_kuliah', [
        'kelas_id' => $kelas->id,
        'hari'     => 'Minggu',
    ]);
});

test('admin dapat mengupdate kelas', function () {
    $kelas = Kelas::create([
        'mata_kuliah_id'    => $this->mk->id,
        'dosen_id'          => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
        'nama_kelas'        => 'Kelas Lama',
        'kapasitas'         => 20,
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.kelas.update', $kelas), [
        'mata_kuliah_id' => $this->mk->id,
        'dosen_id'       => $this->dosen->id,
        'nama_kelas'     => 'Kelas Baru',
        'kapasitas'      => 45,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('kelas', [
        'id'         => $kelas->id,
        'nama_kelas' => 'Kelas Baru',
        'kapasitas'  => 45,
    ]);
});

test('admin dapat menghapus kelas', function () {
    $kelas = Kelas::create([
        'mata_kuliah_id'    => $this->mk->id,
        'dosen_id'          => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
        'nama_kelas'        => 'Kelas Dihapus',
        'kapasitas'         => 20,
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.kelas.destroy', $kelas));

    $response->assertRedirect();
    $this->assertDatabaseMissing('kelas', ['id' => $kelas->id]);
});

test('kelas memerlukan mata kuliah dan dosen yang valid', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.kelas.store'), [
        'mata_kuliah_id' => 9999, // tidak ada
        'dosen_id'       => 9999, // tidak ada
        'nama_kelas'     => 'Kelas Invalid',
        'kapasitas'      => 30,
    ]);

    $response->assertSessionHasErrors(['mata_kuliah_id', 'dosen_id']);
});
