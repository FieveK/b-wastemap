<x-app-layout>
    @section('title', 'Riwayat titik #'.$titikSampah->id.' · B-WasteMap')
    @push('styles')<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">@endpush

    @php
        $statusPeta = $titikSampah->status_peta;
        $statusLabel = ['normal' => 'Belum ada penanganan aktif', 'perlu_perhatian' => 'Menunggu tindak lanjut', 'dalam_penanganan' => 'Sedang ditangani petugas', 'selesai' => 'Selesai ditangani', 'berulang' => 'Dilaporkan kembali'];
        $statusTone = ['normal' => 'bg-[#CFE8E1] text-[#1C8C74]', 'perlu_perhatian' => 'bg-[#fff0dc] text-[#8a4b00]', 'dalam_penanganan' => 'bg-[#0F3F38] text-white', 'selesai' => 'bg-[#CFE8E1] text-[#1C8C74]', 'berulang' => 'bg-[#f0e7f7] text-[#73519a]'];
        $statusColor = ['normal' => '#1C8C74', 'perlu_perhatian' => '#E8871E', 'dalam_penanganan' => '#0F3F38', 'selesai' => '#1C8C74', 'berulang' => '#a6402b'][$statusPeta] ?? '#1C8C74';
        $eventLabel = ['dilaporkan' => 'Laporan diterima', 'laporan_ditautkan' => 'Laporan ditautkan', 'muncul_kembali' => 'Titik dilaporkan kembali', 'ditugaskan' => 'Petugas ditugaskan', 'penanganan_dimulai' => 'Penanganan dimulai', 'dibersihkan' => 'Selesai ditangani'];
        $eventTone = ['dilaporkan' => 'bg-[#eef0e9] text-[#56645b]', 'muncul_kembali' => 'bg-[#f0e7f7] text-[#73519a]', 'ditugaskan' => 'bg-[#fff0dc] text-[#8a4b00]', 'penanganan_dimulai' => 'bg-[#CFE8E1] text-[#286d80]', 'dibersihkan' => 'bg-[#CFE8E1] text-[#1C8C74]'];
        $eventIcons = ['dilaporkan' => '•', 'laporan_ditautkan' => '↔', 'muncul_kembali' => '↻', 'ditugaskan' => '＋', 'penanganan_dimulai' => '↗', 'dibersihkan' => '✓'];
    @endphp

    <main>
        <section class="bg-white py-6 sm:py-8">
            <div class="bw-container">
                <div class="mb-5 text-sm text-[#6c746e]"><a class="text-[#1C8C74]" href="{{ route('peta') }}">Peta publik</a><span class="mx-2">›</span>Detail titik #{{ $titikSampah->id }}</div>
                <div class="flex flex-wrap items-start justify-between gap-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-2"><span class="text-xs font-semibold uppercase tracking-wider text-[#6c746e]">Titik #{{ $titikSampah->id }}</span><span class="map-status {{ $statusTone[$statusPeta] ?? 'bg-gray-100 text-gray-700' }}">{{ $statusLabel[$statusPeta] ?? str_replace('_', ' ', $statusPeta) }}{{ $statusPeta === 'berulang' ? ' · '.$jumlahKemunculanKembali.'×' : '' }}</span></div>
                        <h1 class="mt-2 text-3xl font-semibold tracking-tight">{{ $titikSampah->alamat ?: 'Lokasi sampah #'.$titikSampah->id }}</h1>
                        <p class="mt-2 text-sm text-[#6c746e]">{{ $titikSampah->kelurahan ? $titikSampah->kelurahan.', ' : '' }}{{ $titikSampah->kecamatan ?: 'Kota Banjarmasin' }} · {{ number_format((float) $titikSampah->latitude, 6) }}, {{ number_format((float) $titikSampah->longitude, 6) }}</p>
                    </div>
                    <div class="flex gap-2"><button id="share-point" class="bw-btn bw-btn-outline" type="button">Bagikan</button><a class="bw-btn bw-btn-primary" href="{{ route('peta') }}">Lihat di peta</a></div>
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 border-t border-[#e0e4dc] pt-5 sm:grid-cols-4">
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ $jumlahKemunculanKembali }}×</strong><span class="text-xs text-[#6c746e]">Muncul kembali</span></div>
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ $titikSampah->total_laporan }}</strong><span class="text-xs text-[#6c746e]">Total laporan</span></div>
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ $titikSampah->total_penanganan }}</strong><span class="text-xs text-[#6c746e]">Penanganan selesai</span></div>
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ optional($titikSampah->updated_at)->diffForHumans() ?? '—' }}</strong><span class="text-xs text-[#6c746e]">Pembaruan terakhir</span></div>
                </div>
            </div>
        </section>

        <section class="bg-[#f2f1e9] py-6 sm:py-8">
            <div class="bw-container grid gap-4 lg:grid-cols-[.8fr_1.2fr]">
                <div class="space-y-4">
                    <div class="bw-card overflow-hidden">
                        <div id="point-map" data-bw-map-boundaries class="h-[250px]" aria-label="Peta lokasi titik sampah"></div>
                        <div class="flex items-center justify-between gap-3 p-4 text-sm"><span>{{ $titikSampah->alamat ?: 'Lokasi titik terpilih' }}</span><span class="text-[#6c746e]">Titik #{{ $titikSampah->id }}</span></div>
                    </div>
                    @if ($buktiTerakhir)
                        <article class="bw-card p-4 sm:p-5">
                            <div class="flex items-center justify-between gap-3"><h2 class="text-lg font-semibold">Sebelum &amp; sesudah</h2><span class="text-xs text-[#6c746e]">{{ optional($buktiTerakhir->tanggal_penanganan)->format('d M Y') }}</span></div>
                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <figure class="relative overflow-hidden rounded-lg"><img class="h-44 w-full object-cover" src="{{ asset('storage/'.$buktiTerakhir->foto_sebelum) }}" alt="Kondisi sebelum dibersihkan" loading="lazy"><figcaption class="absolute bottom-2 left-2 rounded-full bg-[#a6402b] px-2 py-1 text-[10px] font-semibold text-white">SEBELUM</figcaption></figure>
                                <figure class="relative overflow-hidden rounded-lg"><img class="h-44 w-full object-cover" src="{{ asset('storage/'.$buktiTerakhir->foto_sesudah) }}" alt="Kondisi sesudah dibersihkan" loading="lazy"><figcaption class="absolute bottom-2 left-2 rounded-full bg-[#1C8C74] px-2 py-1 text-[10px] font-semibold text-white">SESUDAH</figcaption></figure>
                            </div>
                            <div class="mt-3 rounded-lg bg-[#CFE8E1] p-3 text-sm leading-6">@if($buktiTerakhir->volume_m3 !== null){{ number_format((float) $buktiTerakhir->volume_m3, 2, ',', '.') }} m³ sampah diangkut. @endif{{ $buktiTerakhir->catatan }}</div>
                        </article>
                    @else
                        <div class="bw-card p-5"><h2 class="font-semibold">Bukti penanganan</h2><p class="mt-1 text-sm text-[#6c746e]">Foto sebelum dan sesudah akan tampil setelah petugas menyelesaikan penanganan.</p></div>
                    @endif
                </div>

                <section class="bw-card p-5 sm:p-7">
                    <div class="flex flex-wrap items-center justify-between gap-3"><div><p class="bw-kicker">Aktivitas lokasi</p><h2 class="mt-1 text-xl font-semibold">Riwayat titik</h2><p class="mt-1 text-sm text-[#6c746e]">Laporan dan penanganan yang tercatat untuk lokasi ini.</p></div><span class="map-status bg-[#CFE8E1] text-[#286d80]">{{ $riwayat->total() }} aktivitas</span></div>
                    <ol class="mt-6 space-y-0">
                        @forelse ($riwayat as $item)
                            <li class="relative border-l border-[#CFE8E1] pb-7 pl-8 last:border-transparent last:pb-0">
                                <span class="absolute -left-[15px] top-0 grid h-7 w-7 place-items-center rounded-full ring-4 ring-white {{ $eventTone[$item->status_kejadian] ?? 'bg-gray-100 text-gray-600' }}">{{ $eventIcons[$item->status_kejadian] ?? '•' }}</span>
                                <time class="text-xs text-[#7b847d]">{{ $item->created_at->format('d M Y · H:i') }}</time>
                                <h3 class="mt-1 font-semibold">{{ $eventLabel[$item->status_kejadian] ?? str_replace('_', ' ', $item->status_kejadian) }}</h3>
                                <p class="mt-1 text-sm leading-6 text-[#647068]">{{ $item->keterangan }}</p>
                                @if ($item->laporan)
                                    <p class="mt-2 text-xs text-[#7b847d]">{{ $item->laporan->nomor_laporan }}@if($item->laporan->pelapor) · Dilaporkan warga @endif</p>
                                    @if ($item->laporan->penanganan?->status === 'selesai' && $item->laporan->penanganan->petugas)
                                        <p class="mt-1 text-xs text-[#7b847d]">Petugas: {{ $item->laporan->penanganan->petugas->name }}</p>
                                    @endif
                                    @php($penanganan = $item->laporan->penanganan)
                                    @if ($penanganan?->status === 'selesai' && ($penanganan->foto_sebelum || $penanganan->foto_sesudah))
                                        <details class="mt-3 max-w-lg rounded-lg border border-[#e5e8e2] bg-[#fbfcfa] p-3">
                                            <summary class="cursor-pointer text-sm font-semibold text-[#1C8C74]">Lihat foto sebelum dan sesudah</summary>
                                            <div class="mt-3 grid grid-cols-2 gap-2">
                                                @if ($penanganan->foto_sebelum)
                                                    <figure>
                                                        <img class="h-28 w-full rounded-md object-cover" src="{{ asset('storage/'.$penanganan->foto_sebelum) }}" alt="Kondisi sebelum penanganan {{ $item->laporan->nomor_laporan }}" loading="lazy">
                                                        <figcaption class="mt-1 text-xs text-[#6c746e]">Sebelum</figcaption>
                                                    </figure>
                                                @endif
                                                @if ($penanganan->foto_sesudah)
                                                    <figure>
                                                        <img class="h-28 w-full rounded-md object-cover" src="{{ asset('storage/'.$penanganan->foto_sesudah) }}" alt="Kondisi sesudah penanganan {{ $item->laporan->nomor_laporan }}" loading="lazy">
                                                        <figcaption class="mt-1 text-xs text-[#6c746e]">Sesudah</figcaption>
                                                    </figure>
                                                @endif
                                            </div>
                                            @if ($penanganan->volume_m3 !== null)
                                                <p class="mt-2 text-xs text-[#6c746e]">Volume diangkut: {{ number_format((float) $penanganan->volume_m3, 2, ',', '.') }} m³</p>
                                            @endif
                                        </details>
                                    @endif
                                @endif
                            </li>
                        @empty
                            <li class="rounded-xl bg-[#f5f5f0] p-5 text-sm text-[#6c746e]">Belum ada aktivitas yang tercatat.</li>
                        @endforelse
                    </ol>
                    @if ($riwayat->hasPages())
                        <div class="mt-5">{{ $riwayat->links() }}</div>
                    @endif
                </section>
            </div>
        </section>

        <section class="bw-container py-7">
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-[#0A332D] p-5 text-white sm:p-6"><div><h2 class="font-semibold">Melihat sampah muncul kembali?</h2><p class="mt-1 text-sm text-white/75">Buat laporan baru. Admin akan memeriksa apakah lokasinya sama.</p></div><a class="bw-btn bw-btn-orange" href="{{ route('laporan.create') }}">Laporkan titik</a></div>
        </section>
    </main>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        const pointMap = L.map('point-map', { scrollWheelZoom: false, dragging: false, zoomControl: false }).setView([{{ (float) $titikSampah->latitude }}, {{ (float) $titikSampah->longitude }}], 15);
        document.getElementById('point-map').__bwMap = pointMap;
        L.circleMarker([{{ (float) $titikSampah->latitude }}, {{ (float) $titikSampah->longitude }}], { radius: 9, color: '#fff', weight: 2, fillColor: @json($statusColor), fillOpacity: .95 }).addTo(pointMap);
        document.getElementById('share-point').addEventListener('click', async () => {
            const shareData = { title: document.title, url: window.location.href };
            try {
                if (navigator.share) await navigator.share(shareData);
                else { await navigator.clipboard.writeText(window.location.href); document.getElementById('share-point').textContent = 'Tautan disalin'; }
            } catch (error) { if (error.name !== 'AbortError') document.getElementById('share-point').textContent = 'Salin URL dari bilah alamat'; }
        });
        </script>
    @endpush
</x-app-layout>
