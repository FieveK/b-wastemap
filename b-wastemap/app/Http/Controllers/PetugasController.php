<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Penanganan;
use App\Models\RiwayatTitik;

class PetugasController extends Controller
{
    public function index()
    {
        $tugas = Penanganan::where('petugas_id', Auth::id())
            ->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
            ->with('laporan.titikSampah')
            ->get();
        return view('petugas.tugas', compact('tugas'));
    }

    public function update(Request $request, Penanganan $penanganan)
    {
        $data = $request->validate([
            'status' => 'required|in:dalam_penanganan,selesai',
            'foto_sebelum' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto_sesudah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'catatan' => 'nullable|string',
        ]);

        if ($request->hasFile('foto_sebelum')) {
            $data['foto_sebelum'] = $request->file('foto_sebelum')->store('penanganan', 'public');
        }
        if ($request->hasFile('foto_sesudah')) {
            $data['foto_sesudah'] = $request->file('foto_sesudah')->store('penanganan', 'public');
        }

        $penanganan->update($data);

        if ($data['status'] === 'selesai') {
            $penanganan->update(['tanggal_penanganan' => now()]);
            $penanganan->laporan->titikSampah->increment('total_penanganan');

            RiwayatTitik::create([
                'titik_sampah_id' => $penanganan->laporan->titik_sampah_id,
                'laporan_id' => $penanganan->laporan_id,
                'status_kejadian' => 'dibersihkan',
                'keterangan' => $data['catatan'] ?? 'Penanganan selesai dilakukan.',
            ]);
        }

        return back()->with('success', 'Status penanganan diperbarui.');
    }
}