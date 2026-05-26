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
        Schema::create('syarat_ujian_prodi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->enum('jenis_ujian', ['proposal', 'hasil', 'sidang']);
            $table->string('nama_persyaratan');
            $table->string('file_name_key');
            $table->boolean('is_required')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('syarat_ujian_prodi');
    }
};
