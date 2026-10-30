<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\User;
use App\Services\TitikSampahService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminLaporanController extends Controller
{
    public function index()
    {
        $service = app(TitikSampahService::class);
        $laporanBaru = Laporan::where('status', 'menunggu_verifikasi')
            ->orderByRaw("CASE potensi_bahaya WHEN 'keduanya' THEN 0 WHEN 'menghalangi_jalan' THEN 1 WHEN 'dekat_aliran_air' THEN 2 ELSE 3 END")
            ->oldest()
            ->paginate(10, ['*'], 'verifikasi_page');
        $laporanBaru->each(function (Laporan $laporan) use ($service) {
            $laporan->kandidatDalamRadius = $service->kandidatDalamRadius(
                (float) $laporan->latitude,
                (float) $laporan->longitude
            );
        });

        $laporanDiverifikasi = Laporan::where('status', 'diverifikasi')
            ->whereDoesntHave('penanganan')
            ->with('titikSampah')
            ->latest()
            ->paginate(10, ['*'], 'penugasan_page');

        $petugas = User::where('role', 'petugas')->where('is_active', true)->get();

        return view('admin.laporan.index', compact('laporanBaru', 'laporanDiverifikasi', 'petugas'));
    }

    public function verifikasi(Request $request, Laporan $laporan, TitikSampahService $service)
    {
        $data = $request->validate([
            'pilihan_titik' => 'required|in:lama,baru',
            'titik_sampah_id' => 'nullable|required_if:pilihan_titik,lama|integer|exists:titik_sampah,id',
            'alasan_keputusan' => 'required|string|max:300',
        ]);

        $service->prosesVerifikasi(
            $laporan,
            $data['pilihan_titik'],
            isset($data['titik_sampah_id']) ? (int) $data['titik_sampah_id'] : null,
            $data['alasan_keputusan']
        );

        return back()->with('success', 'Laporan diverifikasi dan keputusan tercatat.');
    }

    public function tolak(Request $request, Laporan $laporan)
    {
        $data = $request->validate(['alasan_keputusan' => 'required|string|max:300']);

        DB::transaction(function () use ($laporan, $data) {
            $laporan = Laporan::query()->lockForUpdate()->findOrFail($laporan->id);

            if ($laporan->status !== 'menunggu_verifikasi') {
                throw ValidationException::withMessages(['laporan' => 'Laporan ini sudah diproses.']);
            }

            $laporan->update(['status' => 'ditolak', 'alasan_keputusan' => $data['alasan_keputusan']]);
        });

        return back()->with('success', 'Laporan ditolak.');
    }
}
