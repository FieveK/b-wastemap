<?php
namespace App\Services;

use App\Models\Laporan;
use App\Models\TitikSampah;
use App\Models\RiwayatTitik;
use Illuminate\Support\Facades\DB;

class TitikSampahService
{
    const RADIUS_METER = 50;

    public function prosesVerifikasi(Laporan $laporan): TitikSampah
    {
        $titikTerdekat = $this->cariTitikDalamRadius($laporan->latitude, $laporan->longitude);

        if ($titikTerdekat) {
            $titikTerdekat->increment('total_laporan');
            $titik = $titikTerdekat;
            $statusKejadian = 'muncul_kembali';
            $keterangan = 'Laporan baru masuk pada titik yang sudah pernah tercatat.';
        } else {
            $titik = TitikSampah::create([
                'latitude' => $laporan->latitude,
                'longitude' => $laporan->longitude,
                'status' => 'normal',
                'total_laporan' => 1,
                'total_penanganan' => 0,
            ]);
            $statusKejadian = 'dilaporkan';
            $keterangan = 'Titik baru dibuat dari laporan pertama.';
        }

        $laporan->update(['titik_sampah_id' => $titik->id, 'status' => 'diverifikasi']);

        RiwayatTitik::create([
            'titik_sampah_id' => $titik->id,
            'laporan_id' => $laporan->id,
            'status_kejadian' => $statusKejadian,
            'keterangan' => $keterangan,
        ]);

        $this->perbaruiStatusKeberulangan($titik);

        return $titik;
    }

    private function cariTitikDalamRadius(float $lat, float $lng): ?TitikSampah
    {
        // Formula Haversine: jarak antar 2 koordinat di permukaan bumi (dalam meter)
        $sql = "
            SELECT id, (
                6371000 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS jarak
            FROM titik_sampah
            HAVING jarak <= ?
            ORDER BY jarak ASC
            LIMIT 1
        ";

        $hasil = collect(DB::select($sql, [$lat, $lng, $lat, self::RADIUS_METER]))->first();

        return $hasil ? TitikSampah::find($hasil->id) : null;
    }

    private function perbaruiStatusKeberulangan(TitikSampah $titik): void
    {
        $status = match (true) {
            $titik->total_laporan > 3 => 'berulang',
            $titik->total_laporan >= 2 => 'perlu_perhatian',
            default => 'normal',
        };
        $titik->update(['status' => $status]);
    }
}
?>