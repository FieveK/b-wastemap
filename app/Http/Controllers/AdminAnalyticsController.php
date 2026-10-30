<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\RiwayatTitik;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAnalyticsController extends Controller
{
    private const KECAMATAN = [
        'Banjarmasin Utara',
        'Banjarmasin Selatan',
        'Banjarmasin Timur',
        'Banjarmasin Barat',
        'Banjarmasin Tengah',
    ];

    public function index(Request $request): View
    {
        $filters = $this->filters($request);
        $query = fn () => $this->laporanQuery($filters);

        $totalLaporan = $query()->count();
        $selesai = $query()->whereHas('penanganan', fn ($handling) => $handling->where('status', 'selesai'))->count();
        $menunggu = $query()->where('status', 'menunggu_verifikasi')->count();
        $ditolak = $query()->where('status', 'ditolak')->count();
        $statusLaporan = $query()
            ->select('status')
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('status')
            ->pluck('jumlah', 'status');

        $monthExpression = match (DB::connection()->getDriverName()) {
            'sqlite' => "strftime('%Y-%m', created_at)",
            'pgsql' => "TO_CHAR(created_at, 'YYYY-MM')",
            default => "DATE_FORMAT(created_at, '%Y-%m')",
        };
        $laporanBulanan = $query()
            ->selectRaw("{$monthExpression} AS bulan")
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $kejadianBerulang = RiwayatTitik::query()
            ->where('status_kejadian', 'muncul_kembali')
            ->whereBetween('created_at', [$filters['mulai'], $filters['sampai']])
            ->when($filters['kecamatan'], fn ($history) => $history->whereHas(
                'titikSampah',
                fn ($point) => $point->where('kecamatan', $filters['kecamatan'])
            ));

        $titikBerulang = (clone $kejadianBerulang)->distinct()->count('titik_sampah_id');
        $riwayatTeratas = (clone $kejadianBerulang)
            ->select('titik_sampah_id')
            ->selectRaw('COUNT(*) AS jumlah_kemunculan')
            ->with('titikSampah:id,alamat,kecamatan')
            ->groupBy('titik_sampah_id')
            ->orderByDesc('jumlah_kemunculan')
            ->limit(10)
            ->get();

        $statusLabel = [
            'menunggu_verifikasi' => 'Menunggu verifikasi',
            'diverifikasi' => 'Diverifikasi',
            'ditolak' => 'Ditolak',
        ];

        return view('admin.analytics.index', compact(
            'filters',
            'totalLaporan',
            'selesai',
            'menunggu',
            'ditolak',
            'statusLaporan',
            'laporanBulanan',
            'titikBerulang',
            'riwayatTeratas',
            'statusLabel',
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $filename = 'bwastemap-laporan-'.$filters['mulai']->format('Ymd').'-'.$filters['sampai']->format('Ymd').'.csv';

        return response()->streamDownload(function () use ($filters) {
            $stream = fopen('php://output', 'w');
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, ['Nomor', 'Tanggal', 'Jenis sampah', 'Kecamatan', 'Alamat', 'Status laporan', 'Status penanganan', 'Latitude', 'Longitude', 'Deskripsi'], ',', '"', '\\');

            $this->laporanQuery($filters)
                ->orderBy('id')
                ->chunkById(500, function ($laporanBatch) use ($stream) {
                    foreach ($laporanBatch as $laporan) {
                        fputcsv($stream, [
                            $laporan->nomor_laporan,
                            $laporan->created_at?->format('Y-m-d H:i:s'),
                            $this->safeCsvText(str_replace('_', ' ', $laporan->jenis_sampah)),
                            $this->safeCsvText($laporan->kecamatan),
                            $this->safeCsvText($laporan->alamat),
                            $this->safeCsvText(str_replace('_', ' ', $laporan->status)),
                            $this->safeCsvText(str_replace('_', ' ', $laporan->penanganan?->status ?? 'belum ditugaskan')),
                            $laporan->latitude,
                            $laporan->longitude,
                            $this->safeCsvText($laporan->deskripsi),
                        ], ',', '"', '\\');
                    }
                });

            fclose($stream);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array{mulai: CarbonImmutable, sampai: CarbonImmutable, kecamatan: ?string}
     */
    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'mulai' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'sampai' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'kecamatan' => ['nullable', Rule::in(self::KECAMATAN)],
        ]);

        $mulai = CarbonImmutable::parse($validated['mulai'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $sampai = CarbonImmutable::parse($validated['sampai'] ?? now()->toDateString())->endOfDay();

        if ($mulai->greaterThan($sampai)) {
            throw ValidationException::withMessages([
                'mulai' => 'Tanggal mulai harus sama atau sebelum tanggal akhir.',
            ]);
        }

        return [
            'mulai' => $mulai,
            'sampai' => $sampai,
            'kecamatan' => $validated['kecamatan'] ?? null,
        ];
    }

    /**
     * @param  array{mulai: CarbonImmutable, sampai: CarbonImmutable, kecamatan: ?string}  $filters
     */
    private function laporanQuery(array $filters): Builder
    {
        return Laporan::query()
            ->with([
                'penanganan:id,laporan_id,status',
            ])
            ->whereBetween('created_at', [$filters['mulai'], $filters['sampai']])
            ->when($filters['kecamatan'], fn ($query) => $query->where('kecamatan', $filters['kecamatan']));
    }

    private function safeCsvText(?string $value): string
    {
        $value = (string) $value;

        return preg_match('/^[\t\r\n ]*[=+\-@]/u', $value) === 1 ? "'".$value : $value;
    }
}
