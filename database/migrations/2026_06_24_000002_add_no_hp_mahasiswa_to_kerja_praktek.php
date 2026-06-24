<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kerja_praktek', function (Blueprint $table) {
            $table->string('no_hp_mahasiswa')->nullable()->after('no_telp_pembimbing');
        });
    }

    public function down(): void
    {
        Schema::table('kerja_praktek', function (Blueprint $table) {
            $table->dropColumn('no_hp_mahasiswa');
        });
    }
};
