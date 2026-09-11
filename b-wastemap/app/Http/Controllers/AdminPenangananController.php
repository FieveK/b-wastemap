<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Laporan;
use App\Models\Penanganan;

class AdminPenangananController extends Controller
{
    public function tugaskan(Request $request, Laporan $laporan)
    {
        $request->validate(['petugas_id' => 'required|exists:users,id']);
        Penanganan::create([
            'laporan_id' => $laporan->id,
            'petugas_id' => $request->petugas_id,
            'status' => 'ditugaskan',
        ]);
        return back()->with('success', 'Petugas ditugaskan.');
    }
}