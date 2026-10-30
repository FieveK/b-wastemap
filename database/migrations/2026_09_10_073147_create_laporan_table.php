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
        Schema::create('laporan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('titik_sampah_id')->nullable()->constrained('titik_sampah')->nullOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('foto');
            $table->text('deskripsi')->nullable();
            $table->string('jenis_sampah')->nullable();
            $table->string('perkiraan_kondisi')->nullable();
            $table->enum('status', ['menunggu_verifikasi', 'diverifikasi', 'ditolak'])->default('menunggu_verifikasi');
            $table->date('tanggal_laporan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('laporan');
    }
};
