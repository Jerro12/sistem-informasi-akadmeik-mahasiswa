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
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->string('kabupaten')->nullable()->after('provinsi');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->string('kabupaten')->nullable()->after('provinsi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn('kabupaten');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropColumn('kabupaten');
        });
    }
};
