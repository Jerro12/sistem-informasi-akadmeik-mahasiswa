<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Krs;
use App\Models\Kelas;
use App\Models\MataKuliah;
use App\Models\KrsDetail;
use App\Models\Pembayaran;
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

    $mhsUser = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswa = Mahasiswa::factory()->create([
        'user_id'  => $mhsUser->id,
        'prodi_id' => $this->prodi->id,
    ]);

    // Create mata kuliah and kelas
    $this->mk = MataKuliah::create([
        'kode_mk'  => 'TI101',
        'nama_mk'  => 'Algoritma',
        'sks'      => 3,
        'semester' => 1,
        'prodi_id' => $this->prodi->id,
        'jenis'    => 'wajib',
    ]);
    $this->kelas = Kelas::create([
        'mata_kuliah_id'    => $this->mk->id,
        'dosen_id'          => $this->dosen->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
        'nama_kelas'        => 'Kelas A',
        'kapasitas'         => 40,
    ]);

    // Setup KRS submitted
    $this->krs = Krs::create([
        'mahasiswa_id'      => $this->mahasiswa->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
        'status'            => 'pending',
    ]);
    KrsDetail::create([
        'krs_id'   => $this->krs->id,
        'kelas_id' => $this->kelas->id,
    ]);
});

// ========== KRS APPROVAL ==========

test('admin dapat melihat daftar KRS yang diajukan', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.krs-approval.index'));
    $response->assertStatus(200);
});

test('admin dapat melihat detail KRS', function () {
    $response = $this->actingAs($this->admin)->get(route('admin.krs-approval.show', $this->krs));
    $response->assertStatus(200);
});

test('admin dapat menyetujui KRS', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.krs-approval.approve', $this->krs));

    $response->assertRedirect();
    $this->assertDatabaseHas('krs', [
        'id'     => $this->krs->id,
        'status' => 'approved',
    ]);
});

test('admin dapat menolak KRS', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.krs-approval.reject', $this->krs));

    $response->assertRedirect();
    $this->assertDatabaseHas('krs', [
        'id'     => $this->krs->id,
        'status' => 'rejected',
    ]);
});

test('mahasiswa tidak dapat mengakses KRS approval admin', function () {
    $mhsUser = $this->mahasiswa->user;

    $response = $this->actingAs($mhsUser)->get(route('admin.krs-approval.index'));

    $response->assertStatus(403);
});
