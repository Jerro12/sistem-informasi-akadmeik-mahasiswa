<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            $table->foreignId('prodi_id')->nullable()->change();
            $table->foreignId('fakultas_id')->nullable()->after('prodi_id')->constrained('fakultas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('dosen', function (Blueprint $table) {
            $table->dropForeign(['fakultas_id']);
            $table->dropColumn('fakultas_id');
            $table->foreignId('prodi_id')->nullable(false)->change();
        });
    }
};
