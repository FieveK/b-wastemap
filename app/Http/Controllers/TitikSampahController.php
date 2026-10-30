<?php

namespace App\Http\Controllers;

use App\Models\Penanganan;
use App\Models\TitikSampah;
use Illuminate\View\View;

class TitikSampahController extends Controller
{
    public function show(TitikSampah $titikSampah): View
    {
        $titikSampah->loadCount([
            'laporan as penanganan_aktif' => fn ($query) => $query->whereHas(
                'penanganan', fn ($penanganan) => $penanganan->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
            ),
            'laporan as laporan_belum_ditangani' => fn ($query) => $query->whereDoesntHave('penanganan'),
        ]);

        $riwayat = $titikSampah->riwayat()
            ->with(['laporan.pelapor', 'laporan.penanganan.petugas'])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15, ['*'], 'riwayat_page');
        $buktiTerakhir = Penanganan::query()
            ->where('status', 'selesai')
            ->whereNotNull('foto_sebelum')
            ->whereNotNull('foto_sesudah')
            ->whereHas('laporan', fn ($laporan) => $laporan->where('titik_sampah_id', $titikSampah->id))
            ->with('laporan')
            ->latest('tanggal_penanganan')
            ->latest('id')
            ->first();
        $jumlahKemunculanKembali = $titikSampah->riwayat()->where('status_kejadian', 'muncul_kembali')->count();

        return view('titik.show', compact('titikSampah', 'riwayat', 'buktiTerakhir', 'jumlahKemunculanKembali'));
    }
}
