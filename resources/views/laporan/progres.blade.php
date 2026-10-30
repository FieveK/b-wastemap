<x-app-layout>
    @section('title', 'Progres laporan · B-WasteMap')
    @php
        $labels = [
            'menunggu_verifikasi' => 'Menunggu pemeriksaan admin',
            'diverifikasi' => 'Terverifikasi · menunggu penugasan',
            'ditugaskan' => 'Petugas sudah ditugaskan',
            'dalam_penanganan' => 'Sedang ditangani petugas',
            'selesai' => 'Penanganan selesai',
            'ditolak' => 'Laporan tidak diterima',
        ];
        $steps = ['menunggu_verifikasi', 'diverifikasi', 'ditugaskan', 'dalam_penanganan', 'selesai'];
        $activeStep = array_search($status, $steps, true);
    @endphp
    <main class="bw-container py-10 sm:py-16">
        <section class="bw-card mx-auto max-w-2xl p-6 sm:p-9">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div><p class="bw-kicker">Progres laporan</p><h1 class="mt-2 text-2xl font-semibold sm:text-3xl">{{ $laporan->nomor_laporan }}</h1></div>
                <span class="map-status {{ $status === 'ditolak' ? 'bg-[#f7e9e5] text-[#a6402b]' : ($status === 'selesai' ? 'bg-[#CFE8E1] text-[#1C8C74]' : 'bg-[#fff0dc] text-[#8a4b00]') }}">{{ $labels[$status] }}</span>
            </div>
            <p class="mt-3 text-sm text-[#58665c]">Pembaruan terakhir {{ $laporan->updated_at->format('d/m/Y · H:i') }}</p>
            @if ($status !== 'ditolak')
                <ol class="mt-7 grid gap-3 sm:grid-cols-5" aria-label="Tahapan laporan">
                    @foreach (['Dikirim', 'Diperiksa', 'Ditugaskan', 'Ditangani', 'Selesai'] as $index => $label)
                        <li class="rounded-xl p-3 text-center text-xs font-semibold {{ $activeStep !== false && $index <= $activeStep ? 'bg-[#CFE8E1] text-[#154734]' : 'bg-[#f1f4ef] text-[#7b847d]' }}">
                            <span class="mx-auto mb-2 grid h-7 w-7 place-items-center rounded-full bg-white">{{ $index + 1 }}</span>{{ $label }}
                        </li>
                    @endforeach
                </ol>
            @else
                <div class="mt-6 rounded-xl border border-[#eac7bd] bg-[#fdf4f1] p-4 text-sm leading-6 text-[#873b2a]">
                    <strong>Alasan:</strong> {{ $laporan->alasan_keputusan ?: 'Silakan laporkan kembali dengan informasi yang diperbaiki.' }}
                </div>
            @endif
            <div class="mt-7 grid gap-4 rounded-xl bg-[#f5f5f0] p-4 text-sm sm:grid-cols-2">
                <p><span class="block text-xs text-[#7b847d]">Wilayah terdeteksi</span><strong class="mt-1 block">{{ $laporan->kelurahan ?: 'Kelurahan belum tersedia' }}, {{ $laporan->kecamatan }}</strong></p>
                <p><span class="block text-xs text-[#7b847d]">Waktu laporan</span><strong class="mt-1 block">{{ $laporan->created_at->format('d/m/Y · H:i') }}</strong></p>
            </div>
            <div class="mt-7 flex flex-wrap gap-3">
                <a class="bw-btn bw-btn-outline" href="{{ route('laporan.lacak') }}">Lacak token lain</a>
                <a class="bw-btn bw-btn-orange" href="{{ route('laporan.create') }}">Laporkan titik lain</a>
            </div>
        </section>
    </main>
</x-app-layout>
