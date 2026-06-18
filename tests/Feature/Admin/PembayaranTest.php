<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Mahasiswa;
use App\Models\TahunAkademik;
use App\Models\Pembayaran;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->superadmin = User::factory()->create(['role' => 'superadmin']);
    $this->fakultas = Fakultas::factory()->create();
    $this->prodi = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);
    $this->tahunAkademik = TahunAkademik::factory()->create(['is_active' => true]);

    $mhsUser = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswa = Mahasiswa::factory()->create([
        'user_id'  => $mhsUser->id,
        'prodi_id' => $this->prodi->id,
    ]);
    $this->mhsUser = $mhsUser;

    // Pembayaran pending (mahasiswa sudah upload bukti)
    $this->pembayaran = Pembayaran::create([
        'mahasiswa_id'      => $this->mahasiswa->id,
        'tahun_akademik_id' => $this->tahunAkademik->id,
        'order_id'          => 'ORD-TEST-001',
        'amount'            => 3000000,
        'status'            => 'pending',
        'bukti_transfer'    => 'bukti/contoh.jpg',
    ]);
});

// ========== PEMBAYARAN ADMIN (SUPERADMIN) ==========

test('superadmin dapat melihat daftar monitoring pembayaran', function () {
    $response = $this->actingAs($this->superadmin)->get(route('admin.pembayaran.index'));
    $response->assertStatus(200);
});

test('superadmin dapat memverifikasi pembayaran', function () {
    $response = $this->actingAs($this->superadmin)
        ->post(route('admin.pembayaran.verify', $this->pembayaran), [
            'status' => 'success',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('pembayaran', [
        'id'     => $this->pembayaran->id,
        'status' => 'success',
    ]);
});




test('admin biasa tidak dapat mengakses halaman pembayaran superadmin', function () {
    $adminBiasa = User::factory()->create(['role' => 'admin_prodi']);

    $response = $this->actingAs($adminBiasa)->get(route('admin.pembayaran.index'));

    $response->assertStatus(403);
});

// ========== PEMBAYARAN MAHASISWA ==========

test('mahasiswa dapat melihat halaman pembayaran', function () {
    $response = $this->actingAs($this->mhsUser)->get(route('mahasiswa.pembayaran.index'));
    $response->assertStatus(200);
});

test('mahasiswa lain tidak dapat melihat bukti pembayaran mahasiswa lain', function () {
    // Buat mahasiswa lain
    $otherUser = User::factory()->create(['role' => 'mahasiswa']);
    $otherMhs  = Mahasiswa::factory()->create([
        'user_id'  => $otherUser->id,
        'prodi_id' => $this->prodi->id,
    ]);

    // mahasiswa lain mencoba akses bukti dari pembayaran mahasiswa pertama
    $response = $this->actingAs($otherUser)
        ->get(route('mahasiswa.pembayaran.bukti', $this->pembayaran));

    $response->assertStatus(403);
});
