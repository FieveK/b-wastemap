<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Penanganan;
use App\Models\TitikSampah;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalTitik = TitikSampah::count();
        $laporanBaru = Laporan::where('status', 'menunggu_verifikasi')->count();
        $dalamPenanganan = Penanganan::where('status', '!=', 'selesai')->count();
        $titikBerulang = TitikSampah::where('status', 'berulang')->count();
        $perKecamatan = TitikSampah::selectRaw('kecamatan, count(*) as jumlah')->groupBy('kecamatan')->pluck('jumlah', 'kecamatan');
        $perStatus = TitikSampah::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('admin.dashboard', compact('totalTitik','laporanBaru','dalamPenanganan','titikBerulang','perKecamatan','perStatus'));
    }
}