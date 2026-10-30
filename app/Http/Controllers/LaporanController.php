<?php

namespace App\Http\Controllers;

use App\Models\Laporan;
use App\Models\User;
use App\Notifications\LaporanMasukNotification;
use App\Services\AlamatKoordinatService;
use App\Services\FotoGeotagService;
use App\Services\TitikSampahService;
use App\Services\WilayahBanjarmasinService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class LaporanController extends Controller
{
    public function create(): View
    {
        return view('laporan.create');
    }

    public function berhasil(): View|RedirectResponse
    {
        if (! session()->has('report_token')) {
            return redirect()->route('laporan.create');
        }

        return view('laporan.berhasil');
    }

    public function extractLokasiFoto(
        Request $request,
        FotoGeotagService $geotag,
        WilayahBanjarmasinService $wilayah,
        AlamatKoordinatService $alamat,
        TitikSampahService $titikService
    ): JsonResponse {
        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg|max:10240',
        ]);

        if (! $geotag->isAvailable()) {
            throw ValidationException::withMessages([
                'foto' => 'Server belum mengaktifkan ekstensi PHP EXIF. Hubungi pengelola aplikasi.',
            ]);
        }

        $coordinates = $geotag->extract($request->file('foto'));

        if ($coordinates === null) {
            throw ValidationException::withMessages([
                'foto' => 'Foto tidak memiliki metadata GPS EXIF yang terbaca. Aktifkan simpan lokasi pada Open Camera, lalu ambil foto baru.',
            ]);
        }

        $area = $wilayah->dariKoordinat($coordinates['latitude'], $coordinates['longitude']);

        if ($area === null) {
            throw ValidationException::withMessages([
                'foto' => 'Koordinat foto berada di luar wilayah Kota Banjarmasin.',
            ]);
        }

        $kandidat = $titikService->kandidatDalamRadius($coordinates['latitude'], $coordinates['longitude'])[0] ?? null;

        return response()->json([
            ...$coordinates,
            ...$area,
            'alamat' => $alamat->cari($coordinates['latitude'], $coordinates['longitude']),
            'kandidat_titik' => $kandidat === null ? null : [
                'id' => $kandidat['titik']->id,
                'latitude' => (float) $kandidat['titik']->latitude,
                'longitude' => (float) $kandidat['titik']->longitude,
                'jarak_meter' => $kandidat['jarak'],
                'alamat' => $kandidat['titik']->alamat,
                'status' => $kandidat['titik']->status,
            ],
        ]);
    }

    public function store(
        Request $request,
        FotoGeotagService $geotag,
        WilayahBanjarmasinService $wilayah,
        AlamatKoordinatService $alamat,
        TitikSampahService $titikService
    ) {
        $data = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'alamat' => 'nullable|string|max:150',
            'foto' => 'required|image|mimes:jpg,jpeg|max:10240',
            'konfirmasi_geotag_visual' => 'accepted',
            'deskripsi' => 'nullable|string|max:300',
            'jenis_sampah' => 'required|in:rumah_tangga,bangunan,lainnya',
            'perkiraan_kondisi' => 'required|string|max:120',
            'potensi_bahaya' => 'required|in:tidak,menghalangi_jalan,dekat_aliran_air,keduanya',
        ]);

        if (! $geotag->isAvailable()) {
            throw ValidationException::withMessages([
                'foto' => 'Server belum mengaktifkan ekstensi PHP EXIF. Hubungi pengelola aplikasi.',
            ]);
        }

        $coordinates = $geotag->extract($request->file('foto'));

        if ($coordinates === null) {
            throw ValidationException::withMessages([
                'foto' => 'Metadata GPS EXIF tidak terbaca dari foto. Gunakan foto asli dari Open Camera dengan simpan lokasi aktif.',
            ]);
        }

        if (abs((float) $data['latitude'] - $coordinates['latitude']) > 0.00001
            || abs((float) $data['longitude'] - $coordinates['longitude']) > 0.00001) {
            throw ValidationException::withMessages([
                'foto' => 'Koordinat foto berubah setelah diperiksa. Pilih kembali foto dan gunakan posisi dari metadata EXIF.',
            ]);
        }

        $area = $wilayah->dariKoordinat($coordinates['latitude'], $coordinates['longitude']);

        if ($area === null) {
            throw ValidationException::withMessages([
                'foto' => 'Koordinat foto berada di luar wilayah Kota Banjarmasin.',
            ]);
        }

        $data['latitude'] = $coordinates['latitude'];
        $data['longitude'] = $coordinates['longitude'];
        $data['kecamatan'] = $area['kecamatan'];
        $data['kelurahan'] = $area['kelurahan'];
        $data['gps_accuracy_meters'] = $coordinates['accuracy'];
        $data['alamat'] = trim((string) ($data['alamat'] ?? '')) ?: $alamat->cari($coordinates['latitude'], $coordinates['longitude']);
        unset($data['konfirmasi_geotag_visual']);

        $foto = $request->file('foto')->store('laporan', 'local');

        if (! $foto) {
            throw new RuntimeException('Foto laporan gagal disimpan.');
        }

        $data['foto'] = $foto;
        $token = 'BWM-'.Str::upper(Str::random(32));
        $oldFoto = null;
        $isDuplicate = false;

        try {
            // ponytail: serializes one day's submissions for the pilot; use spatial cell locks if volume grows.
            [$laporan, $isDuplicate, $oldFoto] = Cache::lock('laporan-submit:'.today()->toDateString(), 30)->block(8, function () use (
                $data,
                $coordinates,
                $titikService,
                $token,
                &$oldFoto,
                &$isDuplicate
            ): array {
                return DB::transaction(function () use ($data, $coordinates, $titikService, $token, &$oldFoto, &$isDuplicate): array {
                    $latitudeDelta = TitikSampahService::RADIUS_METER / 111_320;
                    $longitudeDelta = TitikSampahService::RADIUS_METER / (111_320 * max(cos(deg2rad($coordinates['latitude'])), 0.01));
                    $laporanHariIni = Laporan::query()
                        ->where('tanggal_laporan', today()->toDateString())
                        ->whereIn('status', ['menunggu_verifikasi', 'diverifikasi'])
                        ->whereDoesntHave('penanganan', fn ($query) => $query->where('status', 'selesai'))
                        ->whereBetween('latitude', [$coordinates['latitude'] - $latitudeDelta, $coordinates['latitude'] + $latitudeDelta])
                        ->whereBetween('longitude', [$coordinates['longitude'] - $longitudeDelta, $coordinates['longitude'] + $longitudeDelta])
                        ->lockForUpdate()
                        ->get();

                    $laporanSama = $laporanHariIni
                        ->map(fn (Laporan $laporan): array => [
                            'laporan' => $laporan,
                            'jarak' => $titikService->jarakMeter(
                                $coordinates['latitude'],
                                $coordinates['longitude'],
                                (float) $laporan->latitude,
                                (float) $laporan->longitude
                            ),
                        ])
                        ->filter(fn (array $candidate): bool => $candidate['jarak'] <= TitikSampahService::RADIUS_METER)
                        ->sortBy('jarak')
                        ->first();

                    if ($laporanSama !== null) {
                        $laporan = $laporanSama['laporan'];
                        $oldFoto = $laporan->foto;
                        $laporan->update([
                            ...$data,
                            'submission_count' => $laporan->submission_count + 1,
                        ]);
                        $isDuplicate = true;
                    } else {
                        $laporan = Laporan::create([
                            ...$data,
                            'user_id' => Auth::id(),
                            'status' => 'menunggu_verifikasi',
                            'tanggal_laporan' => today(),
                        ]);
                    }

                    DB::table('laporan_tracking_tokens')->insert([
                        'laporan_id' => $laporan->id,
                        'token_hash' => hash('sha256', $token),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    return [$laporan->fresh(), $isDuplicate, $oldFoto];
                });
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($foto);

            throw $exception;
        }

        if ($oldFoto !== null && $oldFoto !== $foto) {
            Storage::disk('local')->delete($oldFoto);
        }

        try {
            User::query()->where('role', 'admin')->where('is_active', true)->get()
                ->each(fn (User $admin) => $admin->notify(new LaporanMasukNotification(
                    $laporan->nomor_laporan,
                    $laporan->kecamatan,
                    $isDuplicate
                )));
        } catch (Throwable $exception) {
            Log::warning('Notifikasi laporan admin gagal dibuat.', ['laporan_id' => $laporan->id]);
        }

        return redirect()->route('laporan.berhasil')->with([
            'report_token' => $token,
            'report_number' => $laporan->nomor_laporan,
            'report_merged' => $isDuplicate,
        ]);
    }

    public function milikSaya(): View
    {
        $laporan = Laporan::where('user_id', Auth::id())
            ->with(['titikSampah', 'penanganan.petugas'])
            ->latest()
            ->paginate(10, ['*'], 'laporan_page');

        return view('laporan.milik-saya', compact('laporan'));
    }

    public function foto(Laporan $laporan)
    {
        $user = Auth::user();
        $laporan->loadMissing('penanganan');
        $bolehLihat = $user->role === 'admin'
            || $laporan->user_id === $user->id
            || ($user->role === 'petugas' && $laporan->penanganan?->petugas_id === $user->id);

        abort_unless($bolehLihat, 404);

        $disk = Storage::disk('local')->exists($laporan->foto) ? 'local' : 'public';
        abort_unless(Storage::disk($disk)->exists($laporan->foto), 404);

        return Storage::disk($disk)->response($laporan->foto);
    }
}
