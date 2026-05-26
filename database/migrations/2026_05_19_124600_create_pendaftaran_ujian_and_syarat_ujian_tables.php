<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pendaftaran_ujian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswa')->cascadeOnDelete();
            $table->foreignId('skripsi_id')->constrained('skripsi')->cascadeOnDelete();
            $table->enum('jenis_ujian', ['proposal', 'hasil', 'sidang']);
            $table->date('tanggal_ujian')->nullable();
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->string('ruangan')->nullable();
            $table->foreignId('penguji1_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->foreignId('penguji2_id')->nullable()->constrained('dosen')->nullOnDelete();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });

        Schema::create('syarat_ujian_upload', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pendaftaran_ujian_id')->constrained('pendaftaran_ujian')->cascadeOnDelete();
            $table->string('nama_persyaratan');
            $table->string('file_path')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syarat_ujian_upload');
        Schema::dropIfExists('pendaftaran_ujian');
    }
};
