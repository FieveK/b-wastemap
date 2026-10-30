<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LaporanPelacakanController extends Controller
{
    public function index(): View
    {
        return view('laporan.lacak');
    }

    public function cari(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'regex:/^BWM-[A-Z0-9]{32}$/i'],
        ]);

        return redirect()->route('laporan.lacak.token', ['token' => Str::upper($data['token'])]);
    }

    public function tampilkan(string $token): View
    {
        $token = Str::upper($token);
        abort_unless(preg_match('/^BWM-[A-Z0-9]{32}$/', $token) === 1, 404);

        $tokenRecord = DB::table('laporan_tracking_tokens')
            ->where('token_hash', hash('sha256', $token))
            ->first();
        abort_unless($tokenRecord, 404);

        $laporan = Laporan::query()->with(['penanganan', 'titikSampah'])
            ->findOrFail($tokenRecord->laporan_id);
        $penanganan = $laporan->penanganan;
        $status = match (true) {
            $laporan->status === 'ditolak' => 'ditolak',
            $penanganan?->status === 'selesai' => 'selesai',
            $penanganan?->status === 'dalam_penanganan' => 'dalam_penanganan',
            $penanganan?->status === 'ditugaskan' => 'ditugaskan',
            $laporan->status === 'diverifikasi' => 'diverifikasi',
            default => 'menunggu_verifikasi',
        };

        return response()->view('laporan.progres', compact('laporan', 'status'))
            ->withHeaders([
                'Cache-Control' => 'no-store, private',
                'Referrer-Policy' => 'no-referrer',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]);
    }
}
