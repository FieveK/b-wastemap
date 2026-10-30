<?php

namespace App\Http\Controllers;

use App\Models\Penanganan;
use App\Models\TitikSampah;

class PetaController extends Controller
{
    public function landing()
    {
        $stats = [
            'titik' => TitikSampah::count(),
            'ditangani' => TitikSampah::where('total_penanganan', '>', 0)->count(),
            'berulang' => TitikSampah::whereHas('riwayat', fn ($query) => $query->where('status_kejadian', 'muncul_kembali'))->count(),
            'kecamatan' => TitikSampah::whereNotNull('kecamatan')->distinct()->count('kecamatan'),
        ];

        $penangananTerakhir = Penanganan::query()
            ->where('status', 'selesai')
            ->with(['laporan.titikSampah', 'petugas'])
            ->latest('tanggal_penanganan')
            ->first();

        return view('welcome', compact('stats', 'penangananTerakhir'));
    }

    public function index()
    {
        return view('peta.index');
    }

    public function data()
    {
        $titik = TitikSampah::query()
            ->withCount([
                'laporan as penanganan_aktif' => fn ($query) => $query->whereHas(
                    'penanganan', fn ($penanganan) => $penanganan->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
                ),
                'laporan as laporan_belum_ditangani' => fn ($query) => $query->whereDoesntHave('penanganan'),
            ])
            ->get(['id', 'latitude', 'longitude', 'status', 'alamat', 'kecamatan', 'total_laporan', 'total_penanganan']);

        return response()->json($titik->map(fn (TitikSampah $item) => [
            'id' => $item->id,
            'latitude' => (float) $item->latitude,
            'longitude' => (float) $item->longitude,
            'status' => $item->status_peta,
            'url' => route('titik.show', $item, false),
            'alamat' => $item->alamat,
            'kecamatan' => $item->kecamatan,
            'total_laporan' => $item->total_laporan,
            'total_penanganan' => $item->total_penanganan,
        ]));
    }
}
