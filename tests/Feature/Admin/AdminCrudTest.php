<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'superadmin']);
});

test('admin can view fakultas list', function () {
    Fakultas::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.fakultas.index'));
    
    $response->assertStatus(200);
});

test('admin can create fakultas', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.fakultas.store'), [
        'nama' => 'Fakultas Teknik Test',
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('fakultas', ['nama' => 'Fakultas Teknik Test']);
});

test('admin can view prodi list', function () {
    Prodi::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.prodi.index'));
    
    $response->assertStatus(200);
});

test('admin can create prodi', function () {
    $fakultas = Fakultas::factory()->create();

    $response = $this->actingAs($this->admin)->post(route('admin.prodi.store'), [
        'fakultas_id' => $fakultas->id,
        'nama' => 'Teknik Informatika Test',
    ]);
    
    $response->assertRedirect();
    $this->assertDatabaseHas('prodi', ['nama' => 'Teknik Informatika Test']);
});

test('non-admin cannot access admin routes', function () {
    $mahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    $prodi = Prodi::factory()->create();
    \App\Models\Mahasiswa::factory()->create([
        'user_id' => $mahasiswa->id,
        'prodi_id' => $prodi->id,
    ]);

    $response = $this->actingAs($mahasiswa)->get(route('admin.fakultas.index'));
    
    $response->assertStatus(403);
});
