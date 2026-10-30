<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\Penanganan;
use App\Models\RiwayatTitik;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminPenangananController extends Controller
{
    public function tugaskan(Request $request, Laporan $laporan)
    {
        $data = $request->validate(['petugas_id' => 'required|exists:users,id']);

        DB::transaction(function () use ($laporan, $data) {
            $petugas = User::query()->lockForUpdate()->findOrFail($data['petugas_id']);
            abort_unless(
                $petugas->role === 'petugas' && $petugas->is_active,
                422,
                'Akun petugas tidak aktif atau bukan petugas.'
            );
            $laporan = Laporan::query()->lockForUpdate()->findOrFail($laporan->id);
            abort_unless($laporan->status === 'diverifikasi', 409, 'Laporan belum terverifikasi.');
            abort_if($laporan->penanganan()->exists(), 409, 'Laporan sudah ditugaskan.');

            Penanganan::create([
                'laporan_id' => $laporan->id,
                'petugas_id' => $data['petugas_id'],
                'status' => 'ditugaskan',
            ]);

            RiwayatTitik::create([
                'titik_sampah_id' => $laporan->titik_sampah_id,
                'laporan_id' => $laporan->id,
                'status_kejadian' => 'ditugaskan',
                'keterangan' => 'Admin menugaskan petugas untuk menangani lokasi.',
            ]);
        });

        return back()->with('success', 'Petugas ditugaskan.');
    }
}
