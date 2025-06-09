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
        Schema::create('rekognisi_file', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rekognisi_dosen_id')->constrained('rekognisi_dosen')->onDelete('cascade');
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->string('file');
            $table->string('nama_file');
            $table->timestamps();
            $table->softDeletes(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekognisi_file');
    }
};
