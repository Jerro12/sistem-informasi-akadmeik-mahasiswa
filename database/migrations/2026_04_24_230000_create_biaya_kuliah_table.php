<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biaya_kuliah', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_akademik_id')->constrained('tahun_akademik')->onDelete('cascade');
            $table->foreignId('prodi_id')->constrained('prodi')->onDelete('cascade');
            $table->decimal('nominal', 15, 2);
            $table->timestamps();
            
            $table->unique(['tahun_akademik_id', 'prodi_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biaya_kuliah');
    }
};
