<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'superadmin']);
    $this->fakultas = Fakultas::factory()->create();
    $this->prodi = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);
    $this->dosenUser = User::factory()->create(['role' => 'dosen']);
    $this->dosen = Dosen::factory()->create([
        'user_id' => $this->dosenUser->id,
        'prodi_id' => $this->prodi->id,
        'fakultas_id' => $this->fakultas->id,
    ]);
});

// ========== MAHASISWA CRUD ==========

test('admin dapat melihat daftar mahasiswa', function () {
    Mahasiswa::factory()->count(3)->create(['prodi_id' => $this->prodi->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.index'));

    $response->assertStatus(200);
});

test('admin dapat menambah mahasiswa baru', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.mahasiswa.store'), [
        'name'              => 'Mahasiswa Baru',
        'password'          => 'password123',
        'nim'               => '2303199999',
        'prodi_id'          => $this->prodi->id,
        'angkatan'          => 2023,
        'semester_sekarang' => 1,
        'status'            => 'aktif',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('mahasiswa', ['nim' => '2303199999']);
    $this->assertDatabaseHas('users', ['username' => '2303199999']);
});

test('admin dapat melihat detail mahasiswa', function () {
    $mahasiswa = Mahasiswa::factory()->create(['prodi_id' => $this->prodi->id]);

    $response = $this->actingAs($this->admin)->get(route('admin.mahasiswa.show', $mahasiswa));

    $response->assertStatus(200);
});

test('admin dapat mengupdate mahasiswa', function () {
    $mahasiswa = Mahasiswa::factory()->create(['prodi_id' => $this->prodi->id]);

    $response = $this->actingAs($this->admin)->put(route('admin.mahasiswa.update', $mahasiswa), [
        'name'             => 'Nama Diupdate',
        'nim'              => $mahasiswa->nim,
        'prodi_id'         => $this->prodi->id,
        'angkatan'         => $mahasiswa->angkatan,
        'semester_sekarang' => 3,
        'status'           => 'aktif',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('mahasiswa', [
        'id'               => $mahasiswa->id,
        'semester_sekarang' => 3,
    ]);
});

test('admin dapat menghapus mahasiswa', function () {
    $mahasiswa = Mahasiswa::factory()->create(['prodi_id' => $this->prodi->id]);

    $response = $this->actingAs($this->admin)->delete(route('admin.mahasiswa.destroy', $mahasiswa));

    $response->assertRedirect();
    $this->assertDatabaseMissing('mahasiswa', ['id' => $mahasiswa->id]);
});

test('mahasiswa tidak dapat mengakses halaman admin mahasiswa', function () {
    $mhsUser = User::factory()->create(['role' => 'mahasiswa']);
    Mahasiswa::factory()->create(['user_id' => $mhsUser->id, 'prodi_id' => $this->prodi->id]);

    $response = $this->actingAs($mhsUser)->get(route('admin.mahasiswa.index'));

    $response->assertStatus(403);
});
