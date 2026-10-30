<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('laporan')) {
            $userId = collect(Schema::getColumns('laporan'))->firstWhere('name', 'user_id');
            $userForeignKey = collect(Schema::getForeignKeys('laporan'))
                ->first(fn (array $key): bool => ($key['columns'] ?? []) === ['user_id']);
            $replaceUserForeignKey = $userId !== null
                && ($userForeignKey === null || strtolower((string) $userForeignKey['on_delete']) !== 'set null');

            Schema::table('laporan', function (Blueprint $table) use ($userId, $userForeignKey, $replaceUserForeignKey): void {
                if ($replaceUserForeignKey && $userForeignKey !== null) {
                    $table->dropForeign($userForeignKey['name'] ?: ['user_id']);
                }

                if ($userId !== null && ! $userId['nullable']) {
                    $table->unsignedBigInteger('user_id')->nullable()->change();
                }

                if ($replaceUserForeignKey) {
                    $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                }

                if (! Schema::hasColumn('laporan', 'kelurahan')) {
                    $table->string('kelurahan')->nullable();
                }

                if (! Schema::hasColumn('laporan', 'gps_accuracy_meters')) {
                    $table->decimal('gps_accuracy_meters', 8, 2)->unsigned()->nullable();
                }

                if (! Schema::hasColumn('laporan', 'submission_count')) {
                    $table->unsignedSmallInteger('submission_count')->default(1);
                }

                if (! Schema::hasIndex('laporan', ['status', 'tanggal_laporan'])) {
                    $table->index(['status', 'tanggal_laporan']);
                }
            });
        }

        if (Schema::hasTable('titik_sampah') && ! Schema::hasColumn('titik_sampah', 'kelurahan')) {
            Schema::table('titik_sampah', function (Blueprint $table): void {
                $table->string('kelurahan')->nullable();
            });
        }

        if (! Schema::hasTable('laporan_tracking_tokens')) {
            Schema::create('laporan_tracking_tokens', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('laporan_id')->constrained('laporan')->cascadeOnDelete();
                $table->char('token_hash', 64)->unique();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('laporan') && Schema::hasColumn('laporan', 'user_id')
            && DB::table('laporan')->whereNull('user_id')->exists()) {
            throw new RuntimeException('Laporan anonim masih ada. Arsipkan atau kaitkan laporan tersebut sebelum rollback.');
        }

        Schema::dropIfExists('laporan_tracking_tokens');

        if (Schema::hasTable('titik_sampah') && Schema::hasColumn('titik_sampah', 'kelurahan')) {
            Schema::table('titik_sampah', function (Blueprint $table): void {
                $table->dropColumn('kelurahan');
            });
        }

        if (! Schema::hasTable('laporan')) {
            return;
        }

        Schema::table('laporan', function (Blueprint $table): void {
            if (Schema::hasIndex('laporan', ['status', 'tanggal_laporan'])) {
                $table->dropIndex(['status', 'tanggal_laporan']);
            }

            $columns = array_values(array_filter(
                ['kelurahan', 'gps_accuracy_meters', 'submission_count'],
                fn (string $column): bool => Schema::hasColumn('laporan', $column),
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });

        $userId = collect(Schema::getColumns('laporan'))->firstWhere('name', 'user_id');
        if ($userId === null) {
            return;
        }

        $userForeignKey = collect(Schema::getForeignKeys('laporan'))
            ->first(fn (array $key): bool => ($key['columns'] ?? []) === ['user_id']);

        Schema::table('laporan', function (Blueprint $table) use ($userId, $userForeignKey): void {
            if ($userForeignKey !== null) {
                $table->dropForeign($userForeignKey['name'] ?: ['user_id']);
            }

            if ($userId['nullable']) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            }

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
