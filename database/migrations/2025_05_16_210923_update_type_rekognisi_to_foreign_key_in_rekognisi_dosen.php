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
            $table->dropColumn('type_rekognisi');
            $table->foreignId('type_rekognisi_id')->constrained('jenis_rekognisi')->onDelete('cascade');
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
