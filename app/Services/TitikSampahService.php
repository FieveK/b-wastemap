<?php

namespace App\Services;

use App\Models\Laporan;
use App\Models\RiwayatTitik;
use App\Models\TitikSampah;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TitikSampahService
{
    public const RADIUS_METER = 50;

    /**
     * @return list<array{titik: TitikSampah, jarak: int}>
     */
    public function kandidatDalamRadius(float $lat, float $lng): array
    {
        $latitudeDelta = self::RADIUS_METER / 111_320;
        $longitudeDelta = self::RADIUS_METER / (111_320 * max(cos(deg2rad($lat)), 0.01));

        $kandidat = TitikSampah::query()
            ->whereBetween('latitude', [$lat - $latitudeDelta, $lat + $latitudeDelta])
            ->whereBetween('longitude', [$lng - $longitudeDelta, $lng + $longitudeDelta])
            ->get();

        $kandidatDalamRadius = [];

        foreach ($kandidat as $titik) {
            $jarak = $this->jarakMeter($lat, $lng, (float) $titik->latitude, (float) $titik->longitude);

            if ($jarak <= self::RADIUS_METER) {
                $kandidatDalamRadius[] = ['titik' => $titik, 'jarak' => $jarak];
            }
        }

        usort($kandidatDalamRadius, fn (array $a, array $b): int => $a['jarak'] <=> $b['jarak']);

        return array_map(
            fn (array $kandidat): array => ['titik' => $kandidat['titik'], 'jarak' => (int) round($kandidat['jarak'])],
            $kandidatDalamRadius
        );
    }

    public function prosesVerifikasi(Laporan $laporan, string $pilihan, ?int $titikId, string $alasan): TitikSampah
    {
        return DB::transaction(function () use ($laporan, $pilihan, $titikId, $alasan) {
            $laporan = Laporan::query()->lockForUpdate()->findOrFail($laporan->id);

            if ($laporan->status !== 'menunggu_verifikasi') {
                throw ValidationException::withMessages(['laporan' => 'Laporan ini sudah diproses.']);
            }

            if ($pilihan === 'lama') {
                $titik = TitikSampah::query()->lockForUpdate()->find($titikId);

                if (! $titik || $this->jarakMeter(
                    (float) $laporan->latitude,
                    (float) $laporan->longitude,
                    (float) $titik->latitude,
                    (float) $titik->longitude
                ) > self::RADIUS_METER) {
                    throw ValidationException::withMessages([
                        'titik_sampah_id' => 'Pilih titik dalam radius 50 meter dari lokasi laporan.',
                    ]);
                }

                $pernahDitangani = $titik->total_penanganan > 0 || $titik->status === 'berulang';
                $titik->increment('total_laporan');
                $titik->update([
                    'status' => $pernahDitangani ? 'berulang' : ($titik->total_laporan > 1 ? 'perlu_perhatian' : 'normal'),
                    'alamat' => $titik->alamat ?: $laporan->alamat,
                    'kecamatan' => $titik->kecamatan ?: $laporan->kecamatan,
                    'kelurahan' => $titik->kelurahan ?: $laporan->kelurahan,
                ]);
                $kejadian = $pernahDitangani ? 'muncul_kembali' : 'laporan_ditautkan';
                $keterangan = ($pernahDitangani ? 'Sampah muncul kembali pada titik ini. ' : 'Laporan lain ditautkan ke titik ini. ').$alasan;
            } else {
                $titik = TitikSampah::create([
                    'latitude' => $laporan->latitude,
                    'longitude' => $laporan->longitude,
                    'alamat' => $laporan->alamat,
                    'kecamatan' => $laporan->kecamatan,
                    'kelurahan' => $laporan->kelurahan,
                    'status' => 'normal',
                    'total_laporan' => 1,
                    'total_penanganan' => 0,
                ]);
                $kejadian = 'dilaporkan';
                $keterangan = 'Titik baru dibuat. '.$alasan;
            }

            $laporan->update([
                'titik_sampah_id' => $titik->id,
                'status' => 'diverifikasi',
                'alasan_keputusan' => $alasan,
            ]);

            RiwayatTitik::create([
                'titik_sampah_id' => $titik->id,
                'laporan_id' => $laporan->id,
                'status_kejadian' => $kejadian,
                'keterangan' => $keterangan,
            ]);

            return $titik->fresh();
        });
    }

    public function jarakMeter(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLng = deg2rad($lng2 - $lng1);
        $a = sin($deltaLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($deltaLng / 2) ** 2;

        return 2 * 6_371_000 * asin(min(1, sqrt($a)));
    }
}
