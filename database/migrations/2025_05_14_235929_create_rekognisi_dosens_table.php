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
        // penghargaan ganti rekognisi dosen ()
        Schema::create('rekognisi_dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_dosen')->constrained('dosens')->onDelete('cascade');
            $table->foreignId('type_rekognisi_id')->constrained('jenis_rekognisi')->onDelete('cascade');
            $table->string('status_rekognisi')->default('diajukan');

            // Rekognisi Dosen
            $table->string('nama_rekognisi')->nullable();
            $table->string('tempat_rekognisi')->nullable();
            $table->string('waktu_rekognisi')->nullable();
            $table->string('keterangan_rekognisi')->nullable();
            // Penelitian
            $table->string('judul_penelitian')->nullable();
            $table->string('jabatan_penelitian')->nullable();
            $table->string('besaran_dana_penelitian')->nullable();
            $table->string('sumber_dana_penelitian')->nullable();
            // Pengabdian
            $table->string('judul_pengabdian')->nullable();
            $table->string('jenis_kegiatan_pengabdian')->nullable();
            $table->string('lokasi_pengabdian')->nullable();
            $table->string('sumber_dana_pengabdian')->nullable();
            // Publikasi
            $table->string('judul_publikasi')->nullable();
            $table->string('jenis_publikasi')->nullable();
            $table->string('nama_jurnal_publikasi')->nullable();
            $table->string('tahun_terbit_iss_publikasi')->nullable();
            // HKI
            $table->string('judul_karya_hki')->nullable();
            $table->string('nama_pemilik_hki')->nullable();
            $table->string('jenis_hki')->nullable();
            $table->string('tanggal_pengajuan_penerbitan_hki')->nullable();
            
            $table->foreignId('id_admin_terima')->nullable();
            $table->foreignId('id_admin_tolak')->nullable();
            $table->foreignId('id_admin_hapus')->nullable();
            $table->string('alasan_tolak')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rekognisi_dosen');
    }
};
