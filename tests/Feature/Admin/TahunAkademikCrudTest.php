<?php

use App\Models\User;
use App\Models\TahunAkademik;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'superadmin']);
});

// ========== TAHUN AKADEMIK CRUD ==========

test('admin dapat melihat daftar tahun akademik', function () {
    TahunAkademik::factory()->count(3)->create();

    $response = $this->actingAs($this->admin)->get(route('admin.tahun-akademik.index'));

    $response->assertStatus(200);
});

test('admin dapat membuat tahun akademik baru', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tahun-akademik.store'), [
        'tahun'    => '2025/2026',
        'semester' => 'ganjil',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tahun_akademik', [
        'tahun'    => '2025/2026',
        'semester' => 'ganjil',
    ]);
});

test('admin dapat mengaktifkan tahun akademik', function () {
    // Buat 2 tahun akademik, yang kedua akan diaktifkan
    $ta1 = TahunAkademik::factory()->create(['is_active' => true]);
    $ta2 = TahunAkademik::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->admin)->post(route('admin.tahun-akademik.activate', $ta2));

    $response->assertOk();
    $this->assertDatabaseHas('tahun_akademik', [
        'id'        => $ta2->id,
        'is_active' => true,
    ]);
    // Yang lama harus non-aktif
    $this->assertDatabaseHas('tahun_akademik', [
        'id'        => $ta1->id,
        'is_active' => false,
    ]);
});

test('admin dapat mengupdate tahun akademik', function () {
    $ta = TahunAkademik::factory()->create([
        'tahun'    => '2024/2025',
        'semester' => 'Ganjil',
    ]);

    $response = $this->actingAs($this->admin)->put(route('admin.tahun-akademik.update', $ta), [
        'tahun'    => '2024/2025',
        'semester' => 'genap',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('tahun_akademik', [
        'id'       => $ta->id,
        'semester' => 'genap',
    ]);
});

test('admin dapat menghapus tahun akademik yang tidak aktif', function () {
    $ta = TahunAkademik::factory()->create(['is_active' => false]);

    $response = $this->actingAs($this->admin)->delete(route('admin.tahun-akademik.destroy', $ta));

    $response->assertRedirect();
    $this->assertDatabaseMissing('tahun_akademik', ['id' => $ta->id]);
});

test('validasi semester harus ganjil atau genap', function () {
    $response = $this->actingAs($this->admin)->post(route('admin.tahun-akademik.store'), [
        'tahun'    => '',
        'semester' => '',
    ]);

    $response->assertSessionHasErrors(['tahun', 'semester']);
});
