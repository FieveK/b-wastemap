<?php

namespace Tests\Unit;

use App\Services\FotoGeotagService;
use PHPUnit\Framework\TestCase;

class FotoGeotagServiceTest extends TestCase
{
    public function test_decodes_open_camera_gps_coordinates_and_rejects_invalid_metadata(): void
    {
        $service = new FotoGeotagService;

        $this->assertSame([
            'latitude' => -3.3084722,
            'longitude' => 114.5902083,
        ], $service->coordinatesFromExif(['GPS' => [
            'GPSLatitude' => ['3/1', '18/1', '305/10'],
            'GPSLatitudeRef' => 'S',
            'GPSLongitude' => ['114/1', '35/1', '2475/100'],
            'GPSLongitudeRef' => 'E',
        ]]));

        $this->assertNull($service->coordinatesFromExif(['GPS' => [
            'GPSLatitude' => ['91/1', '0/1', '0/1'],
            'GPSLatitudeRef' => 'N',
            'GPSLongitude' => ['114/1', '35/1', '0/1'],
            'GPSLongitudeRef' => 'E',
        ]]));
    }
}
