<?php

namespace App\Services;

use JsonException;
use RuntimeException;

class WilayahBanjarmasinService
{
    private ?array $features = null;

    /** @return array{kecamatan: string, kelurahan: string}|null */
    public function dariKoordinat(float $latitude, float $longitude): ?array
    {
        $kelurahan = null;
        $kecamatan = null;

        foreach ($this->features() as $feature) {
            $properties = $feature['properties'] ?? [];
            $geometry = $feature['geometry'] ?? [];
            $tingkat = $properties['tingkat'] ?? null;

            if (! in_array($tingkat, ['Kelurahan', 'Kecamatan'], true)
                || ! $this->geometryContains($geometry, $latitude, $longitude)) {
                continue;
            }

            if ($tingkat === 'Kelurahan') {
                $kelurahan ??= $properties['nama'] ?? null;
                $kecamatan ??= $properties['kecamatan'] ?? null;
            } else {
                $kecamatan ??= $properties['nama'] ?? null;
            }
        }

        return $kecamatan === null ? null : [
            'kecamatan' => $kecamatan,
            'kelurahan' => $kelurahan ?? '',
        ];
    }

    /** @throws JsonException */
    private function features(): array
    {
        if ($this->features !== null) {
            return $this->features;
        }

        $json = file_get_contents(public_path('data/wilayah-banjarmasin.geojson'));

        if ($json === false) {
            throw new RuntimeException('Berkas batas wilayah tidak dapat dibaca.');
        }

        $data = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        $this->features = $data['features'] ?? [];

        return $this->features;
    }

    private function geometryContains(array $geometry, float $latitude, float $longitude): bool
    {
        $type = $geometry['type'] ?? null;
        $coordinates = $geometry['coordinates'] ?? [];
        $polygons = $type === 'Polygon' ? [$coordinates] : ($type === 'MultiPolygon' ? $coordinates : []);

        foreach ($polygons as $rings) {
            if (! $this->ringContains($rings[0] ?? [], $latitude, $longitude)) {
                continue;
            }

            foreach (array_slice($rings, 1) as $hole) {
                if ($this->ringContains($hole, $latitude, $longitude)) {
                    continue 2;
                }
            }

            return true;
        }

        return false;
    }

    private function ringContains(array $ring, float $latitude, float $longitude): bool
    {
        $inside = false;
        $count = count($ring);

        for ($index = 0, $previous = $count - 1; $index < $count; $previous = $index++) {
            [$x1, $y1] = $ring[$previous];
            [$x2, $y2] = $ring[$index];

            if ($this->onSegment($longitude, $latitude, $x1, $y1, $x2, $y2)) {
                return true;
            }

            if (($y1 > $latitude) !== ($y2 > $latitude)
                && $longitude < (($x2 - $x1) * ($latitude - $y1) / ($y2 - $y1)) + $x1) {
                $inside = ! $inside;
            }
        }

        return $inside;
    }

    private function onSegment(float $x, float $y, float $x1, float $y1, float $x2, float $y2): bool
    {
        $cross = (($y - $y1) * ($x2 - $x1)) - (($x - $x1) * ($y2 - $y1));

        return abs($cross) < 1.0e-10
            && $x >= min($x1, $x2) - 1.0e-10 && $x <= max($x1, $x2) + 1.0e-10
            && $y >= min($y1, $y2) - 1.0e-10 && $y <= max($y1, $y2) + 1.0e-10;
    }
}
