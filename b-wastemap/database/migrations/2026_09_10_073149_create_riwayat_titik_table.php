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
        Schema::create('riwayat_titik', function (Blueprint $table) {
            $table->id();
            $table->foreignId('titik_sampah_id')->constrained('titik_sampah')->cascadeOnDelete();
            $table->foreignId('laporan_id')->nullable()->constrained('laporan')->nullOnDelete();
            $table->enum('status_kejadian', ['dilaporkan', 'diverifikasi', 'dibersihkan', 'muncul_kembali']);
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_titik');
    }
};
