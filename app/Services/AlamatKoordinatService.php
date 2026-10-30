<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AlamatKoordinatService
{
    public function cari(float $latitude, float $longitude): ?string
    {
        $latitude = round($latitude, 5);
        $longitude = round($longitude, 5);
        $key = 'alamat-koordinat:'.number_format($latitude, 5, '.', '').':'.number_format($longitude, 5, '.', '');
        $cached = Cache::get($key);
        if (is_array($cached) && is_string($cached['address'] ?? null)) {
            return $cached['address'];
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'B-WasteMap ('.config('app.url').')',
                'Accept-Language' => 'id',
            ])->connectTimeout(2)->timeout(4)->get('https://nominatim.openstreetmap.org/reverse', [
                'lat' => $latitude,
                'lon' => $longitude,
                'format' => 'jsonv2',
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $address = $response->json('address', []);
            $street = trim(implode(' ', array_filter([
                $address['house_number'] ?? null,
                $address['road'] ?? $address['pedestrian'] ?? $address['path'] ?? null,
            ])));
            $parts = array_filter([
                $street ?: null,
                $address['neighbourhood'] ?? $address['suburb'] ?? $address['village'] ?? null,
                $address['city_district'] ?? null,
            ]);
            $result = implode(', ', array_unique($parts));

            if ($result === '') {
                return null;
            }

            $result = mb_substr($result, 0, 150);
            Cache::put($key, ['address' => $result], now()->addDays(30));

            return $result;
        } catch (\Throwable) {
            return null;
        }
    }
}
