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
        Schema::create('rekognisi_komentar', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('rekognisi_dosen_id');
            $table->unsignedBigInteger('id_admin');
            $table->text('komentar');
            $table->timestamp('created_at')->useCurrent();
            $table->foreign('rekognisi_dosen_id')->references('id')->on('rekognisi_dosen')->onDelete('cascade');
            $table->foreign('id_admin')->references('id')->on('admins')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rekognisi_komentar');
    }
};
