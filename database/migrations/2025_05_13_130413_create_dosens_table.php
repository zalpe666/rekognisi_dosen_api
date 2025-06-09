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
        Schema::create('dosens', function (Blueprint $table) {
            $table->id();
            $table->string('nama');
            $table->string('nidn')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('jabatan');
            $table->string('foto_profil')->nullable(); // disimpan dalam bentuk path ke file
            $table->enum('program_studi', ['TI', 'PDSI']);
            $table->timestamps();
            $table->softDeletes(); // ➕ untuk soft delete
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dosens');
    }
};
