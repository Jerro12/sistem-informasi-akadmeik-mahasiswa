<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\MataKuliah;
use App\Models\Dosen;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'superadmin']);
    $this->fakultas = Fakultas::factory()->create();
    $this->prodi = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);
});

// ========== MATA KULIAH CRUD ==========

test('admin dapat melihat daftar mata kuliah', function () {
    MataKuliah::create([
        'kode_mk'  => 'TI101',
        'nama_mk'  => 'Algoritma dan Pemrograman',
        'sks'      => 3,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.mata-kuliah.index'));

    $response->assertStatus(200);
});

test('admin dapat menambah mata kuliah baru', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.mata-kuliah.store'), [
        'kode_mk'   => 'TI101',
        'nama_mk'   => 'Algoritma dan Pemrograman',
        'sks'       => 3,
        'semester'  => 1,
        'prodi_id'  => $this->prodi->id,
        'jenis'     => 'wajib',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('mata_kuliah', [
        'kode_mk' => 'TI101',
        'nama_mk' => 'Algoritma dan Pemrograman',
    ]);
});

test('admin dapat mengupdate mata kuliah', function () {
    $mk = MataKuliah::create([
        'kode_mk'  => 'TI101',
        'nama_mk'  => 'Algoritma',
        'sks'      => 2,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.mata-kuliah.update', $mk), [
        'kode_mk'  => 'TI101',
        'nama_mk'  => 'Algoritma dan Pemrograman (Updated)',
        'sks'      => 3,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('mata_kuliah', [
        'id'      => $mk->id,
        'nama_mk' => 'Algoritma dan Pemrograman (Updated)',
        'sks'     => 3,
    ]);
});

test('admin dapat menghapus mata kuliah', function () {
    $mk = MataKuliah::create([
        'kode_mk'  => 'TI999',
        'nama_mk'  => 'Mata Kuliah Dihapus',
        'sks'      => 2,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);

    $response = $this->actingAs($this->admin)->delete(route('admin.mata-kuliah.destroy', $mk));

    $response->assertRedirect();
    $this->assertDatabaseMissing('mata_kuliah', ['id' => $mk->id]);
});

test('validasi kode mata kuliah harus unik', function () {
    MataKuliah::create([
        'kode_mk'  => 'TI101',
        'nama_mk'  => 'Algoritma',
        'sks'      => 3,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);

    $response = $this->actingAs($this->admin)->post(route('admin.mata-kuliah.store'), [
        'kode_mk'  => 'TI101', // duplikat
        'nama_mk'  => 'Mata Kuliah Lain',
        'sks'      => 3,
        'semester' => 2,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);

    $response->assertSessionHasErrors('kode_mk');
});
