<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('prodi', function (Blueprint $table) {
            $table->string('nama_ketua_prodi')->nullable()->after('nama');
            $table->string('nidn_ketua_prodi')->nullable()->after('nama_ketua_prodi');
        });

        Schema::table('fakultas', function (Blueprint $table) {
            $table->string('nama_dekan')->nullable()->after('nama');
            $table->string('nama_wakil_dekan1')->nullable()->after('nama_dekan');
        });
    }

    public function down(): void
    {
        Schema::table('prodi', function (Blueprint $table) {
            $table->dropColumn(['nama_ketua_prodi', 'nidn_ketua_prodi']);
        });

        Schema::table('fakultas', function (Blueprint $table) {
            $table->dropColumn(['nama_dekan', 'nama_wakil_dekan1']);
        });
    }
};
