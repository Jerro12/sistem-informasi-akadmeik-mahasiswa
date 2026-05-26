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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->after('fakultas_id')->constrained('prodi')->nullOnDelete();
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('provinsi')->nullable()->after('alamat');
            $table->string('kecamatan')->nullable()->after('provinsi');
            $table->string('kelurahan')->nullable()->after('kecamatan');
            $table->string('desa')->nullable()->after('kelurahan');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->string('provinsi')->nullable()->after('alamat');
            $table->string('kecamatan')->nullable()->after('provinsi');
            $table->string('kelurahan')->nullable()->after('kecamatan');
            $table->string('desa')->nullable()->after('kelurahan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['prodi_id']);
            $table->dropColumn('prodi_id');
        });

        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['provinsi', 'kecamatan', 'kelurahan', 'desa']);
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropColumn(['provinsi', 'kecamatan', 'kelurahan', 'desa']);
        });
    }
};
