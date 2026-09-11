<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\Laporan;
use App\Models\TitikSampah;
use Tests\TestCase;

class TitikSampahServiceTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_laporan_dalam_radius_masuk_ke_titik_yang_sama(): void
    {
        $titik = TitikSampah::factory()->create(['latitude' => -3.3194, 'longitude' => 114.5908, 'total_laporan' => 1]);
        $laporan = Laporan::factory()->create(['latitude' => -3.3195, 'longitude' => 114.5909]); // ~15m dari titik di atas

        app()->make('App\\Services\\TitikSampahService')->prosesVerifikasi($laporan);

        $this->assertEquals(1, TitikSampah::count()); // tidak membuat titik baru
        $this->assertEquals(2, $titik->fresh()->total_laporan);
    }
}
