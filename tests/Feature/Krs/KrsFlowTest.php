<?php

use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\KrsDetail;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Prodi;
use App\Models\TahunAkademik;
use App\Models\User;

beforeEach(function () {
    // Create active tahun akademik
    TahunAkademik::factory()->create(['is_active' => true]);
});

test('mahasiswa can view krs page', function () {
    $ta = TahunAkademik::where('is_active', true)->first() ?? TahunAkademik::factory()->create(['is_active' => true]);
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'prodi_id' => $prodi->id,
        'semester_sekarang' => 5,
    ]);

    // Create successful payment
    \App\Models\Pembayaran::create([
        'mahasiswa_id' => $mahasiswa->id,
        'tahun_akademik_id' => $ta->id,
        'order_id' => 'PAY-TEST-123',
        'amount' => 150000,
        'status' => 'success',
    ]);

    $response = $this->actingAs($user)->get(route('mahasiswa.krs.index'));
    
    $response->assertStatus(200);
});

test('mahasiswa cannot access admin routes', function () {
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($user)->get(route('admin.dashboard'));
    
    $response->assertStatus(403);
});

test('unauthenticated user is redirected to login', function () {
    $response = $this->get(route('mahasiswa.krs.index'));
    
    $response->assertRedirect(route('login'));
});

test('mahasiswa dapat mengambil KRS menggunakan mata_kuliah_id', function () {
    $ta = TahunAkademik::where('is_active', true)->first() ?? TahunAkademik::factory()->create(['is_active' => true]);
    $user = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    $mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $user->id,
        'prodi_id' => $prodi->id,
        'semester_sekarang' => 5,
    ]);

    // Create payment
    \App\Models\Pembayaran::create([
        'mahasiswa_id' => $mahasiswa->id,
        'tahun_akademik_id' => $ta->id,
        'order_id' => 'PAY-TEST-STORE',
        'amount' => 150000,
        'status' => 'success',
    ]);

    // Create Dosen
    $dosenUser = User::factory()->create(['role' => 'dosen']);
    $dosen = Dosen::factory()->create([
        'user_id' => $dosenUser->id,
        'prodi_id' => $prodi->id,
    ]);

    // Create MataKuliah
    $mk = MataKuliah::create([
        'kode_mk' => 'INF502',
        'nama_mk' => 'Software Architecture',
        'sks' => 3,
        'semester' => 5,
        'prodi_id' => $prodi->id,
    ]);

    // Post to krs store route with mata_kuliah_id
    $response = $this->actingAs($user)->post(url('mahasiswa/krs'), [
        'mata_kuliah_id' => $mk->id,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success', 'Mata kuliah berhasil diambil');

    // Verify a Kelas was created and student is enrolled in it
    $kelas = Kelas::where('mata_kuliah_id', $mk->id)->where('tahun_akademik_id', $ta->id)->first();
    expect($kelas)->not->toBeNull();
    
    $detail = KrsDetail::where('kelas_id', $kelas->id)->first();
    expect($detail)->not->toBeNull();
    expect($detail->krs->mahasiswa_id)->toBe($mahasiswa->id);
});
