<x-app-layout>
    @section('title', 'Laporan saya · B-WasteMap')
    <main class="bw-narrow py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="bw-kicker">Pantau laporan</p><h1 class="mt-1 text-3xl font-semibold">Laporan saya</h1><p class="mt-2 text-sm text-[#6c746e]">Lihat keputusan verifikasi dan perkembangan penanganan.</p></div>
            <a class="bw-btn bw-btn-orange" href="{{ route('laporan.create') }}">＋ Laporan baru</a>
        </div>

        @if (session('nomor_laporan'))
            <section class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-900" role="status">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div><p class="font-semibold">Laporan berhasil dikirim</p><p class="mt-1">Nomor laporan <strong id="report-number">{{ session('nomor_laporan') }}</strong> · Menunggu verifikasi admin.</p></div>
                    <div class="flex flex-wrap gap-2"><button id="copy-report-number" class="bw-btn bw-btn-outline bw-btn-small" type="button" data-report-number="{{ session('nomor_laporan') }}">Salin nomor</button><a class="bw-btn bw-btn-primary bw-btn-small" href="#laporan-{{ session('laporan_id') }}">Lihat progres</a></div>
                </div>
                <p id="copy-report-status" class="mt-2 text-xs" aria-live="polite"></p>
            </section>
        @elseif (session('success'))
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('success') }}</div>
        @endif

        <div class="mt-6 space-y-4">
            @forelse ($laporan as $item)
                @php
                    $penanganan = $item->penanganan;
                    $statusLabel = $item->status === 'ditolak' ? 'Ditolak' : ($item->status === 'menunggu_verifikasi' ? 'Menunggu verifikasi' : match ($penanganan?->status) {
                        'ditugaskan' => 'Petugas ditugaskan',
                        'dalam_penanganan' => 'Dalam penanganan',
                        'selesai' => 'Selesai ditangani',
                        default => 'Diverifikasi',
                    });
                    $statusTone = $item->status === 'ditolak' ? 'bg-[#f7e9e5] text-[#a6402b]' : ($item->status === 'menunggu_verifikasi' ? 'bg-[#fff0dc] text-[#8a4b00]' : ($penanganan?->status === 'selesai' ? 'bg-[#CFE8E1] text-[#1C8C74]' : 'bg-[#CFE8E1] text-[#286d80]'));
                    $progress = $item->status === 'ditolak' ? -1 : ($item->status === 'menunggu_verifikasi' ? 0 : match ($penanganan?->status) { 'ditugaskan' => 2, 'dalam_penanganan' => 3, 'selesai' => 4, default => 1 });
                @endphp
                <article id="laporan-{{ $item->id }}" class="bw-card overflow-hidden">
                    <div class="grid sm:grid-cols-[150px_minmax(0,1fr)]">
                        <img class="h-40 w-full object-cover sm:h-full" src="{{ route('laporan.foto', $item) }}" alt="Foto laporan {{ $item->nomor_laporan }}" loading="lazy">
                        <div class="p-5">
                            <div class="flex flex-wrap items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wider text-[#6c746e]">{{ $item->nomor_laporan }}</p><h2 class="mt-1 text-lg font-semibold">{{ str_replace('_', ' ', $item->jenis_sampah) }}</h2></div><span class="map-status {{ $statusTone }}">{{ $statusLabel }}</span></div>
                            <p class="mt-2 text-sm text-[#6c746e]">{{ $item->alamat ?: $item->kecamatan }} · {{ number_format((float) $item->latitude, 5) }}, {{ number_format((float) $item->longitude, 5) }} · {{ $item->created_at->format('d M Y · H:i') }}</p>
                            @if ($item->deskripsi)<p class="mt-3 text-sm leading-6">{{ $item->deskripsi }}</p>@endif

                            @if ($item->status === 'ditolak')
                                <div class="mt-4 rounded-lg bg-[#f7e9e5] p-3 text-sm"><strong>Alasan penolakan</strong><p class="mt-1">{{ $item->alasan_keputusan ?: 'Admin menolak laporan ini.' }}</p></div>
                            @else
                                <ol class="mt-5 grid grid-cols-2 gap-2 text-xs sm:grid-cols-4">
                                    @foreach (['Dikirim', 'Diverifikasi', 'Ditugaskan', 'Dalam penanganan', 'Selesai'] as $index => $label)
                                        <li class="rounded-lg px-3 py-2 {{ $progress >= $index ? 'bg-[#CFE8E1] text-[#1C8C74]' : 'bg-[#f2f3ee] text-[#89918a]' }}"><span class="font-semibold">{{ $index + 1 }}.</span> {{ $label }}</li>
                                    @endforeach
                                </ol>
                            @endif

                            <div class="mt-4 flex flex-wrap items-center gap-x-4 gap-y-2 border-t border-[#e8ebe5] pt-4 text-sm">
                                @if ($item->titikSampah)<a class="font-semibold text-[#1C8C74]" href="{{ route('titik.show', $item->titikSampah) }}">Lihat riwayat titik #{{ $item->titikSampah->id }} →</a>@endif
                                @if ($penanganan?->status === 'selesai' && $penanganan->volume_m3)<span class="text-[#6c746e]">Volume tercatat: {{ number_format((float) $penanganan->volume_m3, 2, ',', '.') }} m³</span>@endif
                            </div>
                        </div>
                    </div>
                </article>
            @empty
                <div class="bw-card p-8 text-center"><span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-[#CFE8E1] text-xl text-[#1C8C74]">＋</span><h2 class="mt-3 font-semibold">Belum ada laporan</h2><p class="mt-1 text-sm text-[#6c746e]">Laporan yang Anda kirim akan tampil di sini.</p><a class="bw-btn bw-btn-primary mt-4" href="{{ route('laporan.create') }}">Buat laporan</a></div>
            @endforelse
        </div>
        @if ($laporan->hasPages())
            <div class="mt-5">{{ $laporan->links() }}</div>
        @endif
    </main>
    @push('scripts')
        <script>
        const copyReportButton = document.getElementById('copy-report-number');
        copyReportButton?.addEventListener('click', async () => {
            const copyStatus = document.getElementById('copy-report-status');
            try {
                await navigator.clipboard.writeText(copyReportButton.dataset.reportNumber);
                copyStatus.textContent = 'Nomor laporan disalin.';
            } catch {
                copyStatus.textContent = 'Penyalinan gagal. Salin nomor laporan secara manual.';
            }
        });
        </script>
    @endpush
</x-app-layout>
