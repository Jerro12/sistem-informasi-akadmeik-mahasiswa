<?php

use App\Models\Prodi;
use App\Models\Fakultas;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $fakultas = Fakultas::create(['nama' => 'Fakultas Teknik']);
    $prodi = Prodi::create([
        'fakultas_id' => $fakultas->id,
        'nama' => 'Sistem Informasi',
    ]);

    $response = $this->post('/register', [
        'role' => 'mahasiswa',
        'name' => 'Test User',
        'username' => '2303113649',
        'prodi_id' => $prodi->id,
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('mahasiswa.dashboard', absolute: false));
});
