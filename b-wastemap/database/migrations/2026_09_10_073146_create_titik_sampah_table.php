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
        Schema::create('titik_sampah', function (Blueprint $table) {
            $table->id();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->string('alamat')->nullable();
            $table->string('kecamatan')->nullable();
            $table->enum('status', ['normal', 'perlu_perhatian', 'berulang'])->default('normal');
            $table->unsignedInteger('total_laporan')->default(0);
            $table->unsignedInteger('total_penanganan')->default(0);
            $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titik_sampah');
    }
};
