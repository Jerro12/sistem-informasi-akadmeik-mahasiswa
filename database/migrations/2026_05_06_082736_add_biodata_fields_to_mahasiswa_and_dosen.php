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
            $table->string('no_hp')->nullable()->after('nim');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('no_hp');
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('foto')->nullable()->after('alamat');
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->string('no_hp')->nullable()->after('nidn');
            $table->enum('jenis_kelamin', ['L', 'P'])->nullable()->after('no_hp');
            $table->string('tempat_lahir')->nullable()->after('jenis_kelamin');
            $table->date('tanggal_lahir')->nullable()->after('tempat_lahir');
            $table->text('alamat')->nullable()->after('tanggal_lahir');
            $table->string('foto')->nullable()->after('alamat');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mahasiswa', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'foto']);
        });

        Schema::table('dosen', function (Blueprint $table) {
            $table->dropColumn(['no_hp', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir', 'alamat', 'foto']);
        });
    }
};
