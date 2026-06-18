<?php

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
