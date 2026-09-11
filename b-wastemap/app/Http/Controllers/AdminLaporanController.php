<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Laporan;
use App\Models\User;

class AdminLaporanController extends Controller
{
    public function index()
    {
        $laporanBaru = Laporan::where('status', 'menunggu_verifikasi')->latest()->get();

        $laporanDiverifikasi = Laporan::where('status', 'diverifikasi')
            ->whereDoesntHave('penanganan')
            ->with('titikSampah')
            ->latest()
            ->get();

        $petugas = User::where('role', 'petugas')->get();

        return view('admin.laporan.index', compact('laporanBaru', 'laporanDiverifikasi', 'petugas'));
    }

    public function verifikasi(Laporan $laporan)
    {
        $service = app()->make('App\\Services\\TitikSampahService');
        $service->prosesVerifikasi($laporan);
        return back()->with('success', 'Laporan diverifikasi & dikaitkan ke titik lokasi.');
    }

    public function tolak(Laporan $laporan)
    {
        $laporan->update(['status' => 'ditolak']);
        return back()->with('success', 'Laporan ditolak.');
    }
}