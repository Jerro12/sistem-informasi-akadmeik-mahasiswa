<?php

use App\Models\User;
use App\Models\Fakultas;
use App\Models\Prodi;
use App\Models\Dosen;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'superadmin']);
    $this->fakultas = Fakultas::factory()->create();
    $this->prodi = Prodi::factory()->create(['fakultas_id' => $this->fakultas->id]);
});

test('admin can view dosen list', function () {
    Dosen::factory()->create([
        'prodi_id' => $this->prodi->id,
        'fakultas_id' => $this->fakultas->id,
    ]);

    $response = $this->actingAs($this->admin)->get(route('admin.dosen.index'));
    
    $response->assertStatus(200);
});

test('admin can create dosen at prodi level', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.dosen.store'), [
        'name' => 'Dosen Prodi',
        'password' => 'password123',
        'nidn' => '1111111111',
        'prodi_id' => (string) $this->prodi->id,
    ]);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('dosen', [
        'nidn' => '1111111111',
        'prodi_id' => $this->prodi->id,
        'fakultas_id' => $this->fakultas->id,
    ]);
    $this->assertDatabaseHas('users', [
        'username' => '1111111111',
        'prodi_id' => $this->prodi->id,
        'fakultas_id' => $this->fakultas->id,
    ]);
});

test('admin can create dosen at fakultas level', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.dosen.store'), [
        'name' => 'Dosen Fakultas',
        'password' => 'password123',
        'nidn' => '2222222222',
        'prodi_id' => 'fakultas_' . $this->fakultas->id,
    ]);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('dosen', [
        'nidn' => '2222222222',
        'prodi_id' => null,
        'fakultas_id' => $this->fakultas->id,
    ]);
    $this->assertDatabaseHas('users', [
        'username' => '2222222222',
        'prodi_id' => null,
        'fakultas_id' => $this->fakultas->id,
    ]);
});

test('admin can create dosen at university level', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.dosen.store'), [
        'name' => 'Dosen Universitas',
        'password' => 'password123',
        'nidn' => '3333333333',
        'prodi_id' => '',
    ]);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('dosen', [
        'nidn' => '3333333333',
        'prodi_id' => null,
        'fakultas_id' => null,
    ]);
    $this->assertDatabaseHas('users', [
        'username' => '3333333333',
        'prodi_id' => null,
        'fakultas_id' => null,
    ]);
});

test('admin can update dosen level', function () {
    $dosen = Dosen::factory()->create([
        'prodi_id' => $this->prodi->id,
        'fakultas_id' => $this->fakultas->id,
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.dosen.update', $dosen), [
        'name' => $dosen->user->name,
        'nidn' => $dosen->nidn,
        'prodi_id' => 'fakultas_' . $this->fakultas->id,
    ]);
    
    $response->assertRedirect();
    
    $this->assertDatabaseHas('dosen', [
        'id' => $dosen->id,
        'prodi_id' => null,
        'fakultas_id' => $this->fakultas->id,
    ]);
});
