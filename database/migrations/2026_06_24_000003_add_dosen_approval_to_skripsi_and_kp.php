<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('skripsi', function (Blueprint $table) {
            $table->boolean('pembimbing1_approved')->nullable()->after('pembimbing2_id');
            $table->text('pembimbing1_catatan')->nullable()->after('pembimbing1_approved');
            $table->boolean('pembimbing2_approved')->nullable()->after('pembimbing1_catatan');
            $table->text('pembimbing2_catatan')->nullable()->after('pembimbing2_approved');
        });

        Schema::table('kerja_praktek', function (Blueprint $table) {
            $table->boolean('pembimbing_approved')->nullable()->after('pembimbing_id');
            $table->text('pembimbing_catatan')->nullable()->after('pembimbing_approved');
        });
    }

    public function down(): void
    {
        Schema::table('skripsi', function (Blueprint $table) {
            $table->dropColumn([
                'pembimbing1_approved', 'pembimbing1_catatan',
                'pembimbing2_approved', 'pembimbing2_catatan',
            ]);
        });

        Schema::table('kerja_praktek', function (Blueprint $table) {
            $table->dropColumn(['pembimbing_approved', 'pembimbing_catatan']);
        });
    }
};
