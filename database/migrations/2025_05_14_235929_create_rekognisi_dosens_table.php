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
        Schema::create('rekognisi_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->enum('type_rekognisi', ['rekognisi publikasi', 'hak kekayaan intelektual', 'pengabdian masyarakat', 'penelitian']);
            
            // rekognisi dosen
            $table->string('bentuk_rekognisi')->nullable();
            $table->string('penyelenggara')->nullable();

            // Publikasi / Karya
            $table->string('judul_karya')->nullable();
            $table->string('penerbit')->nullable();

            // Kekayaan Intelektual
            $table->string('pemegang_hak_cipta')->nullable();
            $table->string('judul_ciptaan')->nullable();

            // Pengabdian
            $table->string('jabatan_pengabdian')->nullable();
            $table->string('jumlah_dana')->nullable();

            // Penelitian
            $table->string('besaran_dana_penelitian')->nullable();
            $table->string('judul_penelitian')->nullable();

            $table->string('bukti')->nullable();
            $table->string('status_rekognisi')->default('pending');

            $table->text('umpan_balik')->nullable();
            $table->unsignedBigInteger('id_admin_umpan_balik')->nullable();

            $table->text('alasan_tolak')->nullable();
            $table->unsignedBigInteger('id_admin_tolak')->nullable();

            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('id_admin_hapus')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekognisi_dosen');
    }
};
