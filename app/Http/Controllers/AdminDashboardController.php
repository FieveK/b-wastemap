<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Penanganan;
use App\Models\TitikSampah;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalTitik = TitikSampah::count();
        $laporanBaru = Laporan::where('status', 'menunggu_verifikasi')->count();
        $dalamPenanganan = Penanganan::where('status', 'dalam_penanganan')->count();
        $titikBerulang = TitikSampah::whereHas('riwayat', fn ($query) => $query->where('status_kejadian', 'muncul_kembali'))->count();
        $selesaiBulanIni = Penanganan::where('status', 'selesai')
            ->whereMonth('tanggal_penanganan', now()->month)
            ->whereYear('tanggal_penanganan', now()->year)
            ->count();
        $perKecamatan = TitikSampah::selectRaw('kecamatan, count(*) as jumlah')->groupBy('kecamatan')->pluck('jumlah', 'kecamatan');
        $perStatus = TitikSampah::query()
            ->withCount([
                'laporan as penanganan_aktif' => fn ($query) => $query->whereHas(
                    'penanganan', fn ($penanganan) => $penanganan->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
                ),
                'laporan as laporan_belum_ditangani' => fn ($query) => $query->whereDoesntHave('penanganan'),
            ])
            ->get()
            ->countBy('status_peta');
        $antrean = Laporan::where('status', 'menunggu_verifikasi')
            ->orderByRaw("CASE potensi_bahaya WHEN 'keduanya' THEN 0 WHEN 'menghalangi_jalan' THEN 1 WHEN 'dekat_aliran_air' THEN 2 ELSE 3 END")
            ->oldest()
            ->take(5)
            ->get();
        $tugasAktif = Penanganan::whereIn('status', ['ditugaskan', 'dalam_penanganan'])
            ->with(['laporan.titikSampah', 'petugas'])
            ->latest()
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalTitik', 'laporanBaru', 'dalamPenanganan', 'titikBerulang', 'selesaiBulanIni',
            'perKecamatan', 'perStatus', 'antrean', 'tugasAktif'
        ));
    }
}
