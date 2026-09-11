<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TitikSampah;

class TitikSampahController extends Controller
{
    public function show(TitikSampah $titikSampah)
    {
        $riwayat = $titikSampah->riwayat()->with('laporan.penanganan')->get();
        return view('titik.show', compact('titikSampah', 'riwayat'));
    }
}