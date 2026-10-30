<x-app-layout>
    @section('title', 'Analitik & laporan · B-WasteMap')

    <main class="bw-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="bw-kicker">Pusat admin</p>
                <h1 class="mt-1 text-3xl font-semibold">Analitik &amp; laporan</h1>
                <p class="mt-2 text-sm text-[#6c746e]">Ringkasan laporan dan titik berulang sesuai periode dan kecamatan.</p>
            </div>
            <a class="bw-btn bw-btn-primary" href="{{ route('admin.analitik.export', ['mulai' => $filters['mulai']->toDateString(), 'sampai' => $filters['sampai']->toDateString(), 'kecamatan' => $filters['kecamatan']]) }}">↓ Unduh CSV</a>
        </div>

        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="GET" action="{{ route('admin.analitik') }}" class="bw-card mt-6 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_1.2fr_auto] lg:items-end">
            <div>
                <label class="bw-label" for="analytics-start">Mulai tanggal</label>
                <input id="analytics-start" class="bw-field" type="date" name="mulai" value="{{ $filters['mulai']->toDateString() }}" required>
            </div>
            <div>
                <label class="bw-label" for="analytics-end">Sampai tanggal</label>
                <input id="analytics-end" class="bw-field" type="date" name="sampai" value="{{ $filters['sampai']->toDateString() }}" required>
            </div>
            <div>
                <label class="bw-label" for="analytics-district">Kecamatan</label>
                <select id="analytics-district" class="bw-field" name="kecamatan">
                    <option value="">Semua kecamatan</option>
                    @foreach (['Banjarmasin Utara', 'Banjarmasin Selatan', 'Banjarmasin Timur', 'Banjarmasin Barat', 'Banjarmasin Tengah'] as $kecamatan)
                        <option value="{{ $kecamatan }}" @selected($filters['kecamatan'] === $kecamatan)>{{ $kecamatan }}</option>
                    @endforeach
                </select>
            </div>
            <button class="bw-btn bw-btn-primary" type="submit">Terapkan filter</button>
        </form>

        <section class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Ringkasan laporan">
            <article class="bw-card p-5">
                <p class="text-sm text-[#6c746e]">Total laporan</p>
                <strong class="mt-2 block text-3xl font-semibold">{{ number_format($totalLaporan) }}</strong>
                <p class="mt-1 text-xs text-[#7b847d]">Pada periode terpilih</p>
            </article>
            <article class="bw-card p-5">
                <p class="text-sm text-[#6c746e]">Menunggu verifikasi</p>
                <strong class="mt-2 block text-3xl font-semibold">{{ number_format($menunggu) }}</strong>
                <p class="mt-1 text-xs text-[#7b847d]">Perlu keputusan admin</p>
            </article>
            <article class="bw-card p-5">
                <p class="text-sm text-[#6c746e]">Selesai ditangani</p>
                <strong class="mt-2 block text-3xl font-semibold">{{ number_format($selesai) }}</strong>
                <p class="mt-1 text-xs text-[#7b847d]">Bukti penanganan tersimpan</p>
            </article>
            <article class="bw-card p-5">
                <p class="text-sm text-[#6c746e]">Titik muncul kembali</p>
                <strong class="mt-2 block text-3xl font-semibold">{{ number_format($titikBerulang) }}</strong>
                <p class="mt-1 text-xs text-[#7b847d]">Tercatat pada riwayat titik</p>
            </article>
        </section>

        @php($maxMonthly = max(1, (int) $laporanBulanan->max('jumlah')))
        @php($maxStatus = max(1, (int) $statusLaporan->max()))
        @php($statusTone = ['menunggu_verifikasi' => '#E8871E', 'diverifikasi' => '#1C8C74', 'ditolak' => '#A6402B'])

        <section class="mt-5 grid gap-4 xl:grid-cols-[1.2fr_.8fr]">
            <article class="bw-card p-5 sm:p-6" aria-labelledby="monthly-chart-heading">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="monthly-chart-heading" class="text-xl font-semibold">Laporan per bulan</h2>
                        <p class="mt-1 text-sm text-[#6c746e]">Jumlah laporan yang masuk pada periode terpilih.</p>
                    </div>
                    <span class="map-status bg-[#CFE8E1] text-[#1C8C74]">{{ $laporanBulanan->count() }} bulan</span>
                </div>
                @if ($laporanBulanan->isNotEmpty())
                    <div class="mt-6 flex flex-col gap-4" role="img" aria-label="Grafik jumlah laporan per bulan">
                        @foreach ($laporanBulanan as $bulan)
                            <div>
                                <div class="mb-1 flex justify-between gap-3 text-sm">
                                    <span>{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $bulan->bulan)->format('M Y') }}</span>
                                    <strong>{{ number_format($bulan->jumlah) }}</strong>
                                </div>
                                <div class="h-2 overflow-hidden rounded-full bg-[#edf0ea]">
                                    <div class="h-full rounded-full bg-[#1C8C74]" style="width: {{ round($bulan->jumlah / $maxMonthly * 100) }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-6 rounded-xl bg-[#f5f7f4] p-5 text-sm text-[#6c746e]">Belum ada laporan pada periode ini.</p>
                @endif
            </article>

            <article class="bw-card p-5 sm:p-6" aria-labelledby="status-heading">
                <div>
                    <h2 id="status-heading" class="text-xl font-semibold">Komposisi status laporan</h2>
                    <p class="mt-1 text-sm text-[#6c746e]">Status verifikasi laporan dalam periode ini.</p>
                </div>
                <div class="mt-6 flex flex-col gap-4">
                    @foreach ($statusLabel as $status => $label)
                        @php($count = (int) $statusLaporan->get($status, 0))
                        <div>
                            <div class="mb-1 flex justify-between gap-3 text-sm"><span>{{ $label }}</span><strong>{{ number_format($count) }}</strong></div>
                            <div class="h-2 overflow-hidden rounded-full bg-[#edf0ea]">
                                <div class="h-full rounded-full" style="width: {{ round($count / $maxStatus * 100) }}%; background: {{ $statusTone[$status] }}"></div>
                            </div>
                        </div>
                    @endforeach
                    <p class="rounded-lg bg-[#f5f7f4] p-3 text-xs leading-5 text-[#6c746e]">{{ number_format($ditolak) }} laporan ditolak. Status verifikasi terpisah dari status penanganan.</p>
                </div>
            </article>
        </section>

        <section class="bw-card mt-5 overflow-hidden" aria-labelledby="repeat-points-heading">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-[#e8ebe5] px-5 py-4">
                <div>
                    <h2 id="repeat-points-heading" class="text-xl font-semibold">Titik berulang pada periode ini</h2>
                    <p class="mt-1 text-sm text-[#6c746e]">Lokasi dengan catatan kejadian muncul kembali terbanyak.</p>
                </div>
                <span class="map-status bg-[#f0e7f7] text-[#73519a]">{{ $riwayatTeratas->count() }} titik</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-sm">
                    <thead class="bg-[#f5f7f4] text-xs uppercase tracking-wide text-[#6c746e]">
                        <tr><th class="px-5 py-3 font-semibold">Lokasi</th><th class="px-4 py-3 font-semibold">Kecamatan</th><th class="px-4 py-3 font-semibold">Kemunculan ulang</th><th class="px-5 py-3 text-right font-semibold">Riwayat</th></tr>
                    </thead>
                    <tbody class="divide-y divide-[#e8ebe5]">
                        @forelse ($riwayatTeratas as $kejadian)
                            <tr>
                                <td class="px-5 py-4 font-medium">{{ $kejadian->titikSampah?->alamat ?: 'Titik #'.$kejadian->titik_sampah_id }}</td>
                                <td class="px-4 py-4">{{ $kejadian->titikSampah?->kecamatan ?: '—' }}</td>
                                <td class="px-4 py-4"><span class="map-status bg-[#f0e7f7] text-[#73519a]">{{ number_format($kejadian->jumlah_kemunculan) }} kali</span></td>
                                <td class="px-5 py-4 text-right"><a class="font-semibold text-[#1C8C74] hover:underline" href="{{ route('titik.show', $kejadian->titik_sampah_id) }}">Buka titik</a></td>
                            </tr>
                        @empty
                            <tr><td class="px-5 py-8 text-center text-sm text-[#6c746e]" colspan="4">Belum ada kejadian muncul kembali pada periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</x-app-layout>
