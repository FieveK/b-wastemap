<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_analytics_and_export_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $filters = [
            'mulai' => now()->subDays(29)->toDateString(),
            'sampai' => now()->toDateString(),
            'kecamatan' => 'Banjarmasin Utara',
        ];

        $this->actingAs($admin)
            ->get(route('admin.analitik', $filters))
            ->assertOk()
            ->assertSee('Analitik & laporan');

        $this->get(route('admin.analitik.export', $filters))
            ->assertOk()
            ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
