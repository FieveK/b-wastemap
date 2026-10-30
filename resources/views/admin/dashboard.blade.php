<x-app-layout>
    @section('title', 'Pusat admin · B-WasteMap')
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush
    <main class="bw-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div><p class="bw-kicker">B-WasteMap · pusat kendali</p><h1 class="mt-1 text-3xl font-semibold">Ringkasan wilayah</h1><p class="mt-2 text-sm text-[#6c746e]">Status laporan dan penanganan dari data aplikasi.</p></div>
            <div class="flex flex-wrap gap-2"><a class="bw-btn bw-btn-outline" href="{{ route('peta') }}">Buka peta publik ↗</a><a class="bw-btn bw-btn-primary" href="{{ route('admin.laporan.index') }}">Periksa antrean →</a></div>
        </div>

        <section class="mt-7 grid gap-3 sm:grid-cols-2 xl:grid-cols-4" aria-label="Statistik wilayah">
            @foreach ([['Menunggu verifikasi', $laporanBaru, 'bg-[#fff0dc] text-[#a45d0a]', 'Antrean admin'], ['Dalam penanganan', $dalamPenanganan, 'bg-[#CFE8E1] text-[#286d80]', 'Petugas sudah mulai'], ['Selesai bulan ini', $selesaiBulanIni, 'bg-[#CFE8E1] text-[#1C8C74]', 'Bukti tersimpan'], ['Titik berulang', $titikBerulang, 'bg-[#f0e7f7] text-[#73519a]', 'Perlu pemantauan']] as [$title, $count, $tone, $caption])
                <article class="bw-card p-5"><div class="flex items-start justify-between gap-2"><p class="text-sm text-[#6c746e]">{{ $title }}</p><span class="grid h-9 w-9 place-items-center rounded-lg {{ $tone }}">●</span></div><strong class="mt-3 block text-3xl font-semibold">{{ number_format($count) }}</strong><p class="mt-1 text-xs text-[#7b847d]">{{ $caption }}</p></article>
            @endforeach
        </section>

        <section class="mt-5 grid gap-4 xl:grid-cols-[1.2fr_.8fr]">
            <article class="bw-card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-2"><div><h2 class="text-xl font-semibold">Titik menurut kecamatan</h2><p class="mt-1 text-sm text-[#6c746e]">Jumlah titik yang tersimpan per wilayah.</p></div><span class="map-status bg-[#CFE8E1] text-[#1C8C74]">{{ $totalTitik }} titik</span></div>
                @php($maxKecamatan = max(1, (int) $perKecamatan->max()))
                <div class="mt-6 space-y-4">
                    @forelse ($perKecamatan as $kecamatan => $jumlah)
                        <div><div class="mb-1 flex justify-between gap-3 text-sm"><span>{{ $kecamatan ?: 'Kecamatan belum dicatat' }}</span><strong>{{ $jumlah }}</strong></div><div class="h-2 overflow-hidden rounded-full bg-[#edf0ea]"><div class="h-full rounded-full bg-[#1C8C74]" style="width: {{ round($jumlah / $maxKecamatan * 100) }}%"></div></div></div>
                    @empty
                        <div class="rounded-xl bg-[#f5f5f0] p-5 text-sm text-[#6c746e]">Belum ada data titik untuk ditampilkan.</div>
                    @endforelse
                </div>
            </article>

            <article class="bw-card p-5 sm:p-6">
                <div class="flex items-start justify-between gap-2"><div><h2 class="text-xl font-semibold">Status titik</h2><p class="mt-1 text-sm text-[#6c746e]">Ringkasan dari status saat ini.</p></div><a class="text-sm font-semibold text-[#1C8C74]" href="{{ route('peta') }}">Lihat peta →</a></div>
                @php($statusNames = ['normal' => 'Belum ada penanganan aktif', 'perlu_perhatian' => 'Menunggu tindak lanjut', 'dalam_penanganan' => 'Sedang ditangani petugas', 'selesai' => 'Selesai ditangani', 'berulang' => 'Dilaporkan kembali'])
                @php($statusColors = ['normal' => '#1C8C74', 'perlu_perhatian' => '#E8871E', 'dalam_penanganan' => '#0F3F38', 'selesai' => '#1C8C74', 'berulang' => '#a6402b'])
                @php($statusTotal = max(1, (int) $perStatus->sum()))
                <div class="mt-6 space-y-4">
                    @foreach ($statusNames as $key => $name)
                        @php($count = (int) $perStatus->get($key, 0))
                        <div><div class="mb-1 flex justify-between text-sm"><span>{{ $name }}</span><strong>{{ $count }}</strong></div><div class="h-2 overflow-hidden rounded-full bg-[#edf0ea]"><div class="h-full rounded-full" style="width: {{ round($count / $statusTotal * 100) }}%; background: {{ $statusColors[$key] }}"></div></div></div>
                    @endforeach
                </div>
                <div class="mt-6 overflow-hidden rounded-xl border border-[#e5e8e2] bg-[#f4f4ed]">
                    <div id="admin-monitoring-map" data-bw-map-boundaries class="h-56" aria-label="Peta monitoring titik sampah"></div>
                    <a class="block p-3 text-sm font-semibold text-[#1C8C74]" href="{{ route('peta') }}">Buka peta publik dan filter →</a>
                </div>
            </article>
        </section>

        <section class="mt-5 grid gap-4 lg:grid-cols-2">
            <article class="bw-card p-5 sm:p-6">
                <div class="flex items-center justify-between gap-3"><div><h2 class="text-xl font-semibold">Antrean verifikasi</h2><p class="mt-1 text-sm text-[#6c746e]">Laporan terbaru yang menunggu keputusan.</p></div><a class="text-sm font-semibold text-[#1C8C74]" href="{{ route('admin.laporan.index') }}">Semua antrean →</a></div>
                <div class="mt-4 divide-y divide-[#e8ebe5]">
                    @forelse ($antrean as $laporan)
                        <a href="{{ route('admin.laporan.index') }}" class="flex items-center justify-between gap-3 py-3 text-sm no-underline hover:text-[#1C8C74]"><span><strong class="block">{{ $laporan->nomor_laporan }}</strong><span class="text-xs text-[#6c746e]">{{ $laporan->created_at->diffForHumans() }}</span></span><span class="map-status bg-[#fff0dc] text-[#8a4b00]">Periksa</span></a>
                    @empty
                        <p class="py-5 text-sm text-[#6c746e]">Tidak ada laporan menunggu verifikasi.</p>
                    @endforelse
                </div>
            </article>

            <article class="bw-card p-5 sm:p-6">
                <div><h2 class="text-xl font-semibold">Tugas aktif</h2><p class="mt-1 text-sm text-[#6c746e]">Penanganan yang sedang berjalan.</p></div>
                <div class="mt-4 divide-y divide-[#e8ebe5]">
                    @forelse ($tugasAktif as $tugas)
                        <div class="flex items-center justify-between gap-3 py-3 text-sm"><span><strong class="block">{{ $tugas->laporan?->nomor_laporan }}</strong><span class="text-xs text-[#6c746e]">{{ $tugas->laporan?->titikSampah?->alamat ?: 'Lokasi belum memiliki alamat' }} · {{ $tugas->petugas?->name }}</span></span><span class="map-status bg-[#CFE8E1] text-[#286d80]">{{ str_replace('_', ' ', $tugas->status) }}</span></div>
                    @empty
                        <p class="py-5 text-sm text-[#6c746e]">Belum ada tugas aktif.</p>
                    @endforelse
                </div>
            </article>
        </section>
    </main>
    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        (() => {
            const map = L.map('admin-monitoring-map', { scrollWheelZoom: false }).setView([-3.3194, 114.5908], 12);
            document.getElementById('admin-monitoring-map').__bwMap = map;
            const colors = { normal: '#1C8C74', selesai: '#1C8C74', perlu_perhatian: '#E8871E', dalam_penanganan: '#0F3F38', berulang: '#a6402b' };
            fetch('/api/titik-sampah')
                .then((response) => response.ok ? response.json() : [])
                .then((points) => points.forEach((point) => {
                    L.circleMarker([point.latitude, point.longitude], {
                        radius: point.status === 'berulang' ? 9 : 7,
                        color: '#fff', weight: 2, fillColor: colors[point.status] || '#6c746e', fillOpacity: .95,
                    }).addTo(map).bindPopup(`<strong>Titik #${point.id}</strong><br>${L.Util.escapeHTML(point.alamat || 'Alamat belum dicatat')}<br><a href="${point.url}">Lihat riwayat</a>`);
                }))
                .catch(() => {});
        })();
        </script>
    @endpush
</x-app-layout>
