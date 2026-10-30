<x-app-layout>
    @section('title', 'Verifikasi laporan · B-WasteMap')
    <main class="bw-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="bw-kicker">Pusat admin</p><h1 class="mt-1 text-3xl font-semibold">Verifikasi laporan</h1><p class="mt-2 text-sm text-[#6c746e]">Periksa bukti dan lokasi, lalu catat alasan keputusan.</p></div>
            <a class="bw-btn bw-btn-outline" href="{{ route('admin.dashboard') }}">← Ringkasan admin</a>
        </div>

        @if (session('success'))<div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">Periksa kembali keputusan dan alasan yang wajib diisi.</div>@endif

        <section class="mt-7">
            <div class="mb-4 flex items-center justify-between gap-3"><h2 class="text-xl font-semibold">Menunggu verifikasi</h2><span class="map-status bg-[#fff0dc] text-[#8a4b00]">{{ $laporanBaru->total() }} laporan</span></div>
            <div class="space-y-4">
                @forelse ($laporanBaru as $laporan)
                    <article class="bw-card overflow-hidden">
                        <div class="grid lg:grid-cols-[300px_minmax(0,1fr)]">
                            <div class="bg-[#e9ece6]">
                                <a href="{{ route('laporan.foto', $laporan) }}" target="_blank" rel="noopener" aria-label="Buka foto laporan ukuran penuh">
                                    <img class="h-full min-h-64 max-h-[430px] w-full bg-[#e9ece6] object-contain" src="{{ route('laporan.foto', $laporan) }}" alt="Foto {{ str_replace('_', ' ', $laporan->jenis_sampah) }}" loading="lazy">
                                </a>
                            </div>
                            <div class="p-5 sm:p-6">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div><p class="text-xs font-semibold uppercase tracking-wider text-[#6c746e]">{{ $laporan->nomor_laporan }}</p><h3 class="mt-1 text-xl font-semibold">{{ str_replace('_', ' ', $laporan->jenis_sampah) }}</h3></div>
                                    <span class="map-status bg-[#fff0dc] text-[#8a4b00]">Menunggu verifikasi</span>
                                </div>
                                <p class="mt-2 text-sm text-[#68736b]">{{ $laporan->created_at->format('d M Y · H:i') }} · {{ $laporan->submission_count }} kiriman digabung</p>
                                @if ($laporan->potensi_bahaya !== 'tidak')
                                    <p class="mt-2"><span class="map-status bg-[#f7e9e5] text-[#a6402b]">Prioritas keselamatan · {{ str_replace('_', ' ', $laporan->potensi_bahaya) }}</span></p>
                                @endif
                                @if ($laporan->created_at->lte(now()->subHours(24)))
                                    <p class="mt-2"><span class="map-status bg-[#f7e9e5] text-[#a6402b]">Lewat target verifikasi 24 jam</span></p>
                                @endif
                                <div class="mt-4 grid gap-3 rounded-xl bg-[#f5f5f0] p-4 text-sm sm:grid-cols-2">
                                    <p><span class="block text-xs text-[#7b847d]">Kondisi</span><strong class="mt-1 block">{{ str_replace('_', ' ', $laporan->perkiraan_kondisi ?: 'Belum diisi') }}</strong></p>
                                    <p><span class="block text-xs text-[#7b847d]">Potensi bahaya</span><strong class="mt-1 block">{{ str_replace('_', ' ', $laporan->potensi_bahaya) }}</strong></p>
                                    <p class="sm:col-span-2"><span class="block text-xs text-[#7b847d]">Wilayah terdeteksi</span><strong class="mt-1 block">{{ $laporan->alamat ?: 'Alamat belum tersedia' }} · {{ $laporan->kelurahan ? $laporan->kelurahan.', ' : '' }}{{ $laporan->kecamatan }}</strong></p>
                                    <p class="sm:col-span-2"><span class="block text-xs text-[#7b847d]">Koordinat</span><strong class="mt-1 block">{{ number_format((float) $laporan->latitude, 6) }}, {{ number_format((float) $laporan->longitude, 6) }}</strong><span class="mt-1 block text-xs text-[#68736b]">Cocokkan koordinat ini dengan stempel lokasi yang terlihat pada foto.</span></p>
                                    <p><span class="block text-xs text-[#7b847d]">Akurasi GPS foto</span><strong class="mt-1 block">{{ $laporan->gps_accuracy_meters !== null ? '±'.number_format((float) $laporan->gps_accuracy_meters, 0).' m' : 'Tidak tercatat' }}</strong></p>
                                    @if ($laporan->deskripsi)<p class="sm:col-span-2"><span class="block text-xs text-[#7b847d]">Keterangan warga</span><span class="mt-1 block">{{ $laporan->deskripsi }}</span></p>@endif
                                </div>

                                <form method="POST" action="{{ route('admin.laporan.verifikasi', $laporan) }}" class="mt-5">
                                    @csrf
                                    @if ($laporan->kandidatDalamRadius)
                                        <div class="rounded-xl border border-[#d9cbed] bg-[#f5effb] p-4">
                                            <p class="text-xs font-semibold uppercase tracking-wider text-[#73519a]">{{ count($laporan->kandidatDalamRadius) }} kandidat dalam radius 50 meter</p>
                                            <p class="mt-1 text-sm text-[#635777]">Pilih titik yang sesuai. Daftar diurutkan dari jarak terdekat; keputusan akhir tetap pada admin.</p>
                                            <fieldset class="mt-3 space-y-2">
                                                <legend class="sr-only">Pilih titik sampah untuk ditautkan</legend>
                                                @foreach ($laporan->kandidatDalamRadius as $kandidat)
                                                    <label class="flex cursor-pointer items-start gap-3 rounded-lg bg-white p-3">
                                                        <input class="mt-1 rounded border-gray-300 text-[#1C8C74] focus:ring-[#1C8C74]" type="radio" name="titik_sampah_id" value="{{ $kandidat['titik']->id }}" @checked((string) old('titik_sampah_id', $loop->first ? $kandidat['titik']->id : '') === (string) $kandidat['titik']->id)>
                                                        <span class="min-w-0"><strong class="block">Titik #{{ $kandidat['titik']->id }} · {{ $kandidat['titik']->alamat ?: 'Alamat belum dicatat' }}</strong><span class="mt-1 block text-xs text-[#635777]">{{ $kandidat['jarak'] }} m · {{ $kandidat['titik']->total_laporan }} laporan · {{ str_replace('_', ' ', $kandidat['titik']->status) }}</span></span>
                                                    </label>
                                                @endforeach
                                            </fieldset>
                                            <x-input-error :messages="$errors->get('titik_sampah_id')" class="mt-2" />
                                            <div class="mt-3 flex flex-wrap gap-2">
                                                <button class="bw-btn bw-btn-primary bw-btn-small" type="submit" name="pilihan_titik" value="lama">Terima &amp; tautkan ke titik dipilih</button>
                                                <button class="bw-btn bw-btn-outline bw-btn-small" type="submit" name="pilihan_titik" value="baru">Terima sebagai titik baru</button>
                                            </div>
                                        </div>
                                    @else
                                        <div class="rounded-xl border border-[#CFE8E1] bg-[#f6f7f3] p-4">
                                            <p class="text-sm font-semibold">Tidak ada titik dalam radius 50 meter.</p>
                                            <p class="mt-1 text-sm text-[#6c746e]">Setelah diperiksa, laporan dapat diterima sebagai titik baru.</p>
                                            <button class="bw-btn bw-btn-primary bw-btn-small mt-3" type="submit" name="pilihan_titik" value="baru">Terima sebagai titik baru</button>
                                        </div>
                                    @endif
                                    <div class="mt-4">
                                        <label class="bw-label" for="reason-{{ $laporan->id }}">Alasan keputusan <span class="font-normal text-[#7b847d]">(wajib)</span></label>
                                        <textarea id="reason-{{ $laporan->id }}" name="alasan_keputusan" class="bw-field" rows="2" maxlength="300" required placeholder="Contoh: Lokasi sesuai titik lama; sampah muncul kembali.">{{ old('alasan_keputusan') }}</textarea>
                                    </div>
                                </form>

                                <form method="POST" action="{{ route('admin.laporan.tolak', $laporan) }}" class="mt-3 flex flex-col gap-3 border-t border-[#e5e8e2] pt-4 sm:flex-row sm:items-end">
                                    @csrf
                                    <div class="min-w-0 flex-1"><label class="bw-label" for="reject-{{ $laporan->id }}">Alasan penolakan</label><input id="reject-{{ $laporan->id }}" class="bw-field" name="alasan_keputusan" maxlength="300" required placeholder="Jelaskan alasan laporan ditolak."></div>
                                    <button class="bw-btn border border-[#eac7bd] bg-white text-[#a6402b]" type="submit">Tolak laporan</button>
                                </form>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="bw-card p-8 text-center"><span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-[#CFE8E1] text-xl text-[#1C8C74]">✓</span><h3 class="mt-3 font-semibold">Antrean verifikasi kosong</h3><p class="mt-1 text-sm text-[#6c746e]">Laporan baru akan muncul di halaman ini.</p></div>
                @endforelse
            </div>
            @if ($laporanBaru->hasPages())
                <div class="mt-5">{{ $laporanBaru->links() }}</div>
            @endif
        </section>

        <section class="mt-10">
            <div class="mb-4 flex items-center justify-between gap-3"><div><p class="bw-kicker">Langkah selanjutnya</p><h2 class="mt-1 text-xl font-semibold">Diverifikasi · belum ditugaskan</h2></div><span class="map-status bg-[#CFE8E1] text-[#286d80]">{{ $laporanDiverifikasi->total() }} laporan</span></div>
            <div class="grid gap-3 md:grid-cols-2">
                @forelse ($laporanDiverifikasi as $laporan)
                    <article class="bw-card p-4">
                        <div class="flex gap-3"><img src="{{ route('laporan.foto', $laporan) }}" alt="" class="h-20 w-20 rounded-lg object-cover"><div class="min-w-0 flex-1"><p class="text-xs font-semibold text-[#6c746e]">{{ $laporan->nomor_laporan }}</p><h3 class="mt-1 font-semibold">{{ $laporan->titikSampah?->alamat ?: 'Titik #'.($laporan->titik_sampah_id ?? '—') }}</h3><p class="mt-1 text-sm text-[#6c746e]">{{ str_replace('_', ' ', $laporan->jenis_sampah) }} · {{ $laporan->submission_count }} kiriman digabung</p></div></div>
                        @if ($petugas->isNotEmpty())
                            <form method="POST" action="{{ route('admin.penanganan.tugaskan', $laporan) }}" class="mt-4 flex gap-2 border-t border-[#e5e8e2] pt-4">
                                @csrf
                                <label class="sr-only" for="petugas-{{ $laporan->id }}">Pilih petugas aktif</label>
                                <select id="petugas-{{ $laporan->id }}" name="petugas_id" class="bw-field min-w-0" required><option value="">Pilih petugas aktif</option>@foreach ($petugas as $orang)<option value="{{ $orang->id }}">{{ $orang->name }}</option>@endforeach</select>
                                <button class="bw-btn bw-btn-primary bw-btn-small" type="submit">Tugaskan</button>
                            </form>
                        @else
                            <p class="mt-4 border-t border-[#e5e8e2] pt-4 text-sm text-[#6c746e]">Belum ada petugas aktif. Aktifkan atau tambahkan akun petugas di menu Pengguna.</p>
                        @endif
                    </article>
                @empty
                    <div class="bw-card p-5 text-sm text-[#6c746e]">Semua laporan terverifikasi sudah ditugaskan.</div>
                @endforelse
            </div>
            @if ($laporanDiverifikasi->hasPages())
                <div class="mt-5">{{ $laporanDiverifikasi->links() }}</div>
            @endif
        </section>
    </main>
</x-app-layout>
