<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("ALTER TABLE riwayat_titik MODIFY COLUMN status_kejadian ENUM('dilaporkan', 'diverifikasi', 'dibersihkan', 'muncul_kembali', 'laporan_ditautkan', 'ditugaskan', 'penanganan_dimulai') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        if (DB::table('riwayat_titik')->whereIn('status_kejadian', ['laporan_ditautkan', 'ditugaskan', 'penanganan_dimulai'])->exists()) {
            throw new RuntimeException('Riwayat memakai status baru; ubah data tersebut sebelum rollback.');
        }

        DB::statement("ALTER TABLE riwayat_titik MODIFY COLUMN status_kejadian ENUM('dilaporkan', 'diverifikasi', 'dibersihkan', 'muncul_kembali') NOT NULL");
    }
};
