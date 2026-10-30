<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Throwable;

class FotoGeotagService
{
    public function isAvailable(): bool
    {
        return function_exists('exif_read_data');
    }

    public function extract(UploadedFile $foto): ?array
    {
        if (! $this->isAvailable()) {
            return null;
        }

        try {
            $metadata = @exif_read_data($foto->getRealPath(), 'GPS', true, false);
        } catch (Throwable) {
            return null;
        }

        return is_array($metadata) ? $this->coordinatesFromExif($metadata) : null;
    }

    public function coordinatesFromExif(array $metadata): ?array
    {
        $gps = $metadata['GPS'] ?? $metadata;
        $latitude = $this->coordinate($gps['GPSLatitude'] ?? null, 90);
        $longitude = $this->coordinate($gps['GPSLongitude'] ?? null, 180);
        $latitudeRef = strtoupper(trim((string) ($gps['GPSLatitudeRef'] ?? '')));
        $longitudeRef = strtoupper(trim((string) ($gps['GPSLongitudeRef'] ?? '')));

        if ($latitude === null || $longitude === null) {
            return null;
        }

        if (! in_array($latitudeRef, ['N', 'S'], true) || ! in_array($longitudeRef, ['E', 'W'], true)) {
            return null;
        }

        $accuracy = $this->rational($gps['GPSHPositioningError'] ?? null);

        return [
            'latitude' => round($latitude * ($latitudeRef === 'S' ? -1 : 1), 7),
            'longitude' => round($longitude * ($longitudeRef === 'W' ? -1 : 1), 7),
            'accuracy' => $accuracy !== null && $accuracy >= 0 ? round($accuracy, 2) : null,
        ];
    }

    private function coordinate(mixed $parts, float $limit): ?float
    {
        if (! is_array($parts) || count($parts) !== 3) {
            return null;
        }

        $values = array_map(fn (mixed $part): ?float => $this->rational($part), array_values($parts));

        if (in_array(null, $values, true)) {
            return null;
        }

        [$degrees, $minutes, $seconds] = $values;

        if ($degrees < 0 || $minutes < 0 || $minutes >= 60 || $seconds < 0 || $seconds >= 60) {
            return null;
        }

        if ($degrees > $limit || ($degrees === $limit && ($minutes > 0 || $seconds > 0))) {
            return null;
        }

        return $degrees + ($minutes / 60) + ($seconds / 3600);
    }

    private function rational(mixed $value): ?float
    {
        if (is_int($value) || is_float($value)) {
            return is_finite((float) $value) ? (float) $value : null;
        }

        if (! is_string($value) || ! preg_match('/^\s*(-?\d+(?:\.\d+)?)\s*(?:\/\s*(-?\d+(?:\.\d+)?))?\s*$/', $value, $matches)) {
            return null;
        }

        $numerator = (float) $matches[1];
        $denominator = isset($matches[2]) ? (float) $matches[2] : 1.0;

        if ($denominator === 0.0) {
            return null;
        }

        $result = $numerator / $denominator;

        return is_finite($result) ? $result : null;
    }
}
