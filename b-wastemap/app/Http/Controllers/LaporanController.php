<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Laporan;

class LaporanController extends Controller
{
    public function create()
    {
        return view('laporan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_sampah' => 'required|string',
        ]);

        $data['foto'] = $request->file('foto')->store('laporan', 'public');
        $data['user_id'] = Auth::id();
        $data['status'] = 'menunggu_verifikasi';
        $data['tanggal_laporan'] = now();

        Laporan::create($data);

        return redirect()->route('laporan.milik-saya')->with('success', 'Laporan terkirim, menunggu verifikasi admin.');
    }

    public function milikSaya()
    {
        $laporan = Laporan::where('user_id', Auth::id())->latest()->get();
        return view('laporan.milik-saya', compact('laporan'));
    }
}