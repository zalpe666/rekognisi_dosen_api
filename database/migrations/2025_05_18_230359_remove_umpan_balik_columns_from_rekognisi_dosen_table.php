<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('rekognisi_dosen', function (Blueprint $table) {
            $table->dropColumn(['id_admin_umpan_balik', 'umpan_balik']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rekognisi_dosen', function (Blueprint $table) {
            //
        });
    }
};
