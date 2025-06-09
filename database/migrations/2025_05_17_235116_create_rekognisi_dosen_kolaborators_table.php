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
        Schema::create('rekognisi_dosen_kolabolator', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekognisi_dosen_id')->constrained('rekognisi_dosen')->onDelete('cascade');
            $table->foreignId('id_dosen_kolabolator')->constrained('dosens')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekognisi_dosen_kolaborators');
    }
};
