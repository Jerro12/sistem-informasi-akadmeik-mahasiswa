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
        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->decimal('biaya_krs', 15, 2)->default(0)->after('semester');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tahun_akademik', function (Blueprint $table) {
            $table->dropColumn('biaya_krs');
        });
    }
};
