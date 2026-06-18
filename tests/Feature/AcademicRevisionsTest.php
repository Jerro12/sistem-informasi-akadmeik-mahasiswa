<?php

use App\Models\BimbinganSkripsi;
use App\Models\Dosen;
use App\Models\Kelas;
use App\Models\Mahasiswa;
use App\Models\MataKuliah;
use App\Models\Pembayaran;
use App\Models\Prodi;
use App\Models\Skripsi;
use App\Models\TahunAkademik;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    
    // Create active tahun akademik
    $this->ta = TahunAkademik::factory()->create([
        'is_active' => true,
        'semester' => 'ganjil',
    ]);

    // Create Prodi
    $this->prodi = Prodi::factory()->create();

    // Create Dosen Pembimbing 1 & 2
    $this->userDosen1 = User::factory()->create(['role' => 'dosen']);
    $this->dosen1 = Dosen::factory()->create([
        'user_id' => $this->userDosen1->id,
        'prodi_id' => $this->prodi->id,
    ]);

    $this->userDosen2 = User::factory()->create(['role' => 'dosen']);
    $this->dosen2 = Dosen::factory()->create([
        'user_id' => $this->userDosen2->id,
        'prodi_id' => $this->prodi->id,
    ]);

    // Create Mahasiswa
    $this->userMahasiswa = User::factory()->create(['role' => 'mahasiswa']);
    $this->mahasiswa = Mahasiswa::factory()->create([
        'user_id' => $this->userMahasiswa->id,
        'prodi_id' => $this->prodi->id,
        'semester_sekarang' => 5, // odd semester
    ]);

    // Create Payment for KRS
    Pembayaran::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'tahun_akademik_id' => $this->ta->id,
        'order_id' => 'PAY-REV-123',
        'amount' => 150000,
        'status' => 'success',
    ]);
});

test('KRS index automatically creates a Kelas for an offered MataKuliah that does not have a Kelas', function () {
    // Create a MataKuliah matching mahasiswa's prodi, target semester (5), and active year parity (odd/ganjil)
    $mk = MataKuliah::create([
        'kode_mk' => 'INF501',
        'nama_mk' => 'Web Development Advanced',
        'sks' => 3,
        'semester' => 5,
        'prodi_id' => $this->prodi->id,
    ]);

    // Verify no Kelas exists for this MataKuliah initially
    expect(Kelas::where('mata_kuliah_id', $mk->id)->count())->toBe(0);

    // Hit the KRS index page as the student
    $response = $this->actingAs($this->userMahasiswa)->get(route('mahasiswa.krs.index'));
    
    $response->assertStatus(200);

    // Verify a default Kelas has been auto-created
    expect(Kelas::where('mata_kuliah_id', $mk->id)->count())->toBe(1);
    
    $kelas = Kelas::where('mata_kuliah_id', $mk->id)->first();
    expect($kelas->nama_kelas)->toBe('Kelas A');
    expect($kelas->kapasitas)->toBe(40);
    expect($kelas->dosen_id)->toBe($this->dosen1->id);
});

test('Student can specify advisor and advisor 2 can review the bimbingan successfully', function () {
    // Create student's skripsi record
    $skripsi = Skripsi::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'pembimbing1_id' => $this->dosen1->id,
        'pembimbing2_id' => $this->dosen2->id,
        'judul' => 'Research on AI Security',
        'status' => Skripsi::STATUS_BIMBINGAN,
        'tanggal_pengajuan' => now(),
    ]);

    // Student submits a bimbingan note directed to Pembimbing 2
    $file = UploadedFile::fake()->create('chapter1.pdf', 500);

    $response = $this->actingAs($this->userMahasiswa)->post(route('mahasiswa.skripsi.bimbingan.store'), [
        'dosen_id' => $this->dosen2->id,
        'catatan_mahasiswa' => 'Mohon review bab 1 pak.',
        'file_dokumen' => $file,
    ]);

    $response->assertRedirect(route('mahasiswa.skripsi.index'));

    // Check bimbingan record created
    $bimbingan = BimbinganSkripsi::first();
    expect($bimbingan)->not->toBeNull();
    expect($bimbingan->dosen_id)->toBe($this->dosen2->id);
    expect($bimbingan->status)->toBe(BimbinganSkripsi::STATUS_MENUNGGU);

    // Try to review as Pembimbing 2 (should be authorized now)
    $reviewResponse = $this->actingAs($this->userDosen2)->post(route('dosen.skripsi.bimbingan.review', $bimbingan), [
        'catatan_dosen' => 'Sudah bagus, lanjut bab 2.',
        'status' => 'disetujui',
    ]);

    $reviewResponse->assertRedirect();
    
    $bimbingan->refresh();
    expect($bimbingan->status)->toBe(BimbinganSkripsi::STATUS_DISETUJUI);
    expect($bimbingan->catatan_dosen)->toBe('Sudah bagus, lanjut bab 2.');
    expect($bimbingan->dosen_id)->toBe($this->dosen2->id);
});

test('Secure download route restricts access correctly', function () {
    // Create student's skripsi record
    $skripsi = Skripsi::create([
        'mahasiswa_id' => $this->mahasiswa->id,
        'pembimbing1_id' => $this->dosen1->id,
        'pembimbing2_id' => $this->dosen2->id,
        'judul' => 'Research on AI Security',
        'status' => Skripsi::STATUS_BIMBINGAN,
        'tanggal_pengajuan' => now(),
    ]);

    // Create a bimbingan record with file
    $filePath = 'skripsi/bimbingan/test.pdf';
    Storage::disk('public')->put($filePath, 'dummy content');

    $bimbingan = BimbinganSkripsi::create([
        'skripsi_id' => $skripsi->id,
        'dosen_id' => $this->dosen1->id,
        'tanggal_bimbingan' => now(),
        'catatan_mahasiswa' => 'Testing file download',
        'file_dokumen' => $filePath,
        'status' => BimbinganSkripsi::STATUS_MENUNGGU,
    ]);

    // 1. Unrelated user - should be forbidden (403)
    $unrelatedUser = User::factory()->create(['role' => 'mahasiswa']);
    $response = $this->actingAs($unrelatedUser)->get(route('bimbingan.download', $bimbingan));
    $response->assertStatus(403);

    // 2. Student who owns the skripsi - should be allowed (200)
    $responseStudent = $this->actingAs($this->userMahasiswa)->get(route('bimbingan.download', $bimbingan));
    $responseStudent->assertStatus(200);

    // 3. Advisor 2 (Pembimbing 2) - should be allowed (200)
    $responseAdvisor2 = $this->actingAs($this->userDosen2)->get(route('bimbingan.download', $bimbingan));
    $responseAdvisor2->assertStatus(200);

    // 4. Admin Prodi from same prodi - should be allowed (200)
    $adminProdiUser = User::factory()->create([
        'role' => 'admin_prodi',
        'prodi_id' => $this->prodi->id,
    ]);
    $responseAdminProdi = $this->actingAs($adminProdiUser)->get(route('bimbingan.download', $bimbingan));
    $responseAdminProdi->assertStatus(200);

    // 5. Admin Prodi from different prodi - should be forbidden (403)
    $otherProdi = Prodi::factory()->create();
    $otherAdminProdiUser = User::factory()->create([
        'role' => 'admin_prodi',
        'prodi_id' => $otherProdi->id,
    ]);
    $responseOtherAdminProdi = $this->actingAs($otherAdminProdiUser)->get(route('bimbingan.download', $bimbingan));
    $responseOtherAdminProdi->assertStatus(403);
});

test('admin_prodi can see all lecturers in their faculty in Class CRUD Dosen select list', function () {
    // Create another prodi under the same faculty
    $otherProdiSameFaculty = Prodi::factory()->create([
        'fakultas_id' => $this->prodi->fakultas_id,
    ]);

    // Create a dosen in that other prodi
    $userDosenOtherProdi = User::factory()->create(['role' => 'dosen']);
    $dosenOtherProdi = Dosen::factory()->create([
        'user_id' => $userDosenOtherProdi->id,
        'prodi_id' => $otherProdiSameFaculty->id,
    ]);

    // Create a prodi under a different faculty
    $otherFaculty = \App\Models\Fakultas::factory()->create();
    $otherProdiDiffFaculty = Prodi::factory()->create([
        'fakultas_id' => $otherFaculty->id,
    ]);

    // Create a dosen in that different faculty
    $userDosenDiffFaculty = User::factory()->create(['role' => 'dosen']);
    $dosenDiffFaculty = Dosen::factory()->create([
        'user_id' => $userDosenDiffFaculty->id,
        'prodi_id' => $otherProdiDiffFaculty->id,
    ]);

    // Create admin prodi for our main prodi
    $adminProdiUser = User::factory()->create([
        'role' => 'admin_prodi',
        'prodi_id' => $this->prodi->id,
    ]);

    // Access class CRUD index page
    $response = $this->actingAs($adminProdiUser)->get(route('admin.kelas.index'));
    
    $response->assertStatus(200);

    // Verify lecturers list passed to the view contains lecturers in the same faculty
    $viewDosen = $response->viewData('dosen');
    expect($viewDosen->pluck('id'))->toContain($this->dosen1->id);
    expect($viewDosen->pluck('id'))->toContain($dosenOtherProdi->id);
    // and excludes lecturers from a different faculty
    expect($viewDosen->pluck('id'))->not->toContain($dosenDiffFaculty->id);
});
