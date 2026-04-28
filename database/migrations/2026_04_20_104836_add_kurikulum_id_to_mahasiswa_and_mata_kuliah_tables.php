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
        Schema::table('mata_kuliah', function (Blueprint $table) {
            $table->foreignId('kurikulum_id')->nullable()->constrained('kurikulum')->nullOnDelete();
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->foreignId('kurikulum_id')->nullable()->constrained('kurikulum')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa_and_mata_kuliah_tables', function (Blueprint $table) {
            //
        });
    }
};
