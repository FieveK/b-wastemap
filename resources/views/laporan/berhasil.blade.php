<x-app-layout>
    @section('title', 'Laporan terkirim · B-WasteMap')
    @php($token = session('report_token'))
    @php($trackingUrl = route('laporan.lacak.token', ['token' => $token]))
    <main class="bw-container py-10 sm:py-16">
        <section class="bw-card mx-auto max-w-2xl p-6 text-center sm:p-10">
            <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[#CFE8E1] text-2xl font-bold text-[#1C8C74]" aria-hidden="true">✓</span>
            <p class="bw-kicker mt-5">Laporan terkirim</p>
            <h1 class="mt-2 text-2xl font-semibold sm:text-3xl">Simpan token progres</h1>
            @if (session('report_merged'))
                <p class="mt-3 text-sm leading-6 text-[#58665c]">Laporan ini digabung dengan laporan aktif pada titik dan hari yang sama. Bukti terbaru akan dipakai admin.</p>
            @else
                <p class="mt-3 text-sm leading-6 text-[#58665c]">Simpan token atau unduh QR. Token memberi akses untuk melihat status laporan; siapa pun yang memilikinya dapat melihat progres.</p>
            @endif
            <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-[#6c746e]">{{ session('report_number') }}</p>
            <div class="mt-2 flex flex-wrap items-center justify-center gap-2">
                <code id="tracking-token" class="rounded-lg bg-[#f1f4ef] px-4 py-3 text-lg font-bold tracking-widest text-[#0A332D]">{{ $token }}</code>
                <button id="copy-tracking-token" class="bw-btn bw-btn-outline" type="button">Salin token</button>
            </div>
            <p id="tracking-token-status" class="mt-2 text-sm text-[#58665c]" aria-live="polite"></p>
            <canvas id="tracking-qr" data-url="{{ $trackingUrl }}" class="mx-auto mt-5 rounded-xl bg-white p-2" aria-label="QR Code token progres laporan"></canvas>
            <p id="tracking-qr-status" class="text-sm text-[#58665c]" aria-live="polite">Menyiapkan QR…</p>
            <button id="download-tracking-qr" class="bw-btn bw-btn-primary mt-4" type="button" disabled>Unduh QR PNG</button>
            <div class="mt-6 flex flex-wrap justify-center gap-3">
                <a class="bw-btn bw-btn-outline" href="{{ $trackingUrl }}">Lihat progres</a>
                <a class="bw-btn bw-btn-orange" href="{{ route('laporan.create') }}">Buat laporan baru</a>
            </div>
            <p class="mt-6 text-xs leading-5 text-[#6c746e]">Laporan dapat dikirim tanpa akun. Halaman progres tidak menampilkan identitas pelapor, foto privat, atau koordinat persis.</p>
        </section>
    </main>
</x-app-layout>
