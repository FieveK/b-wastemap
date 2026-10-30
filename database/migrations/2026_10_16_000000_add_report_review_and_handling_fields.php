<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laporan', function (Blueprint $table) {
            if (!Schema::hasColumn('laporan', 'alamat')) {
                $table->string('alamat', 150)->nullable();
            }
            if (!Schema::hasColumn('laporan', 'kecamatan')) {
                $table->string('kecamatan', 80)->nullable();
            }
            if (!Schema::hasColumn('laporan', 'potensi_bahaya')) {
                $table->string('potensi_bahaya', 32)->default('tidak');
            }
            if (!Schema::hasColumn('laporan', 'alasan_keputusan')) {
                $table->text('alasan_keputusan')->nullable();
            }
        });

        Schema::table('penanganan', function (Blueprint $table) {
            if (!Schema::hasColumn('penanganan', 'volume_m3')) {
                $table->decimal('volume_m3', 8, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('penanganan', 'volume_m3')) {
            Schema::table('penanganan', function (Blueprint $table) {
                $table->dropColumn('volume_m3');
            });
        }

        $reportColumns = array_values(array_filter(
            ['potensi_bahaya', 'alasan_keputusan'],
            fn (string $column): bool => Schema::hasColumn('laporan', $column),
        ));

        if ($reportColumns !== []) {
            Schema::table('laporan', fn (Blueprint $table) => $table->dropColumn($reportColumns));
        }

        // alamat and kecamatan may predate this migration; retain them on rollback.
    }
};
