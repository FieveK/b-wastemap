<?php

namespace App\Http\Controllers;

use App\Models\Penanganan;
use App\Models\RiwayatTitik;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PetugasController extends Controller
{
    public function index()
    {
        $tugas = Penanganan::where('petugas_id', Auth::id())
            ->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
            ->with('laporan.titikSampah')
            ->orderBy('created_at')
            ->orderBy('id')
            ->paginate(10, ['*'], 'tugas_page');

        return view('petugas.tugas', compact('tugas'));
    }

    public function update(Request $request, Penanganan $penanganan)
    {
        $data = $request->validate([
            'status' => 'required|in:dalam_penanganan,selesai',
            'foto_sebelum' => 'required_if:status,selesai|nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto_sesudah' => 'required_if:status,selesai|nullable|image|mimes:jpg,jpeg,png|max:2048',
            'volume_m3' => 'required_if:status,selesai|nullable|numeric|min:0|max:100000',
            'catatan' => 'nullable|string|max:1000',
        ]);

        abort_unless($penanganan->petugas_id === Auth::id(), 403);

        $storedFiles = [];

        try {
            DB::transaction(function () use ($request, $penanganan, $data, &$storedFiles) {
                $penanganan = Penanganan::query()->lockForUpdate()->with('laporan.titikSampah')->findOrFail($penanganan->id);

                if ($penanganan->petugas_id !== Auth::id() || $penanganan->status === 'selesai') {
                    throw ValidationException::withMessages(['status' => 'Tugas ini sudah ditutup atau bukan milik Anda.']);
                }

                $titik = $penanganan->laporan->titikSampah;
                abort_unless($titik, 409, 'Titik laporan tidak ditemukan.');

                foreach (['foto_sebelum', 'foto_sesudah'] as $field) {
                    if ($request->hasFile($field)) {
                        $path = $request->file($field)->store('penanganan', 'public');

                        if (! $path) {
                            throw new RuntimeException('Foto penanganan gagal disimpan.');
                        }

                        $storedFiles[] = $path;
                        $data[$field] = $path;
                    }
                }

                $statusSebelumnya = $penanganan->status;
                $penanganan->update($data);

                if ($data['status'] === 'dalam_penanganan' && $statusSebelumnya !== 'dalam_penanganan') {
                    RiwayatTitik::create([
                        'titik_sampah_id' => $titik->id,
                        'laporan_id' => $penanganan->laporan_id,
                        'status_kejadian' => 'penanganan_dimulai',
                        'keterangan' => $data['catatan'] ?? 'Petugas mulai menangani titik.',
                    ]);
                }

                if ($data['status'] === 'selesai') {
                    $penanganan->update(['tanggal_penanganan' => now()]);
                    $titik->increment('total_penanganan');
                    $titik->update(['status' => 'normal']);

                    RiwayatTitik::create([
                        'titik_sampah_id' => $titik->id,
                        'laporan_id' => $penanganan->laporan_id,
                        'status_kejadian' => 'dibersihkan',
                        'keterangan' => ($data['catatan'] ?? null) ?: 'Penanganan selesai dilakukan.',
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($storedFiles);

            throw $exception;
        }

        return back()->with('success', 'Status dan bukti penanganan tersimpan.');
    }
}
