<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0A332D">
    <meta name="description" content="Peta publik dan pelaporan titik sampah liar di Kota Banjarmasin.">
    <link rel="icon" type="image/png" href="{{ asset('images/b-wastemap-logo-badge.png') }}">
    <title>B-WasteMap · Petakan, Pantau, dan Tangani</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    @include('layouts.navigation')

    <main>
        <section class="bw-container grid items-center gap-10 py-14 lg:grid-cols-[.95fr_1.05fr] lg:py-20">
            <div>
                <p class="bw-kicker mb-4">Sistem pemetaan &amp; monitoring · Kota Banjarmasin</p>
                <h1 class="max-w-2xl text-4xl font-semibold leading-[1.08] tracking-tight text-[#0A332D] sm:text-5xl lg:text-[3.65rem]">Setiap titik sampah punya riwayatnya sendiri.</h1>
                <p class="mt-5 max-w-xl text-lg leading-8 text-[#626d65]">B-WasteMap mencatat lokasi sampah liar dari laporan warga sampai penanganan, lalu menjaga riwayatnya jika muncul kembali.</p>
                <div class="mt-7 flex flex-wrap gap-3">
                    <a class="bw-btn bw-btn-primary" href="{{ route('peta') }}">Lihat peta kota <span aria-hidden="true">→</span></a>
                    <a class="bw-btn bw-btn-outline" href="{{ route('laporan.create') }}">Laporkan titik</a>
                </div>
                <div class="mt-9 grid max-w-xl grid-cols-2 gap-5 border-t border-[#CFE8E1] pt-6 sm:grid-cols-4">
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ number_format($stats['titik']) }}</strong><span class="text-xs text-[#6c746e]">Titik terpetakan</span></div>
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ number_format($stats['ditangani']) }}</strong><span class="text-xs text-[#6c746e]">Pernah ditangani</span></div>
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ number_format($stats['berulang']) }}</strong><span class="text-xs text-[#6c746e]">Titik berulang</span></div>
                    <div><strong class="block text-2xl font-semibold text-[#0A332D]">{{ number_format($stats['kecamatan']) }}</strong><span class="text-xs text-[#6c746e]">Kecamatan tercatat</span></div>
                </div>
            </div>
            <div class="relative">
                <div class="overflow-hidden rounded-2xl border border-[#CFE8E1] bg-[#e6e6dc] shadow-lg">
                    <div id="home-map" data-bw-map-boundaries class="h-[310px] sm:h-[380px]" role="region" aria-label="Peta titik sampah liar di Banjarmasin"></div>
                </div>
                <div class="mt-3 flex flex-wrap gap-2 text-xs text-[#4e5a52]">
                    <span class="map-status bg-white"><i class="h-2.5 w-2.5 rounded-full bg-[#1C8C74]"></i>Selesai / belum aktif</span>
                    <span class="map-status bg-white"><i class="h-2.5 w-2.5 rounded-full bg-[#E8871E]"></i>Menunggu tindak lanjut</span>
                    <span class="map-status bg-white"><i class="h-2.5 w-2.5 rounded-full bg-[#0F3F38]"></i>Sedang ditangani</span>
                    <span class="map-status bg-white"><i class="h-2.5 w-2.5 rounded-full bg-[#a6402b]"></i>Dilaporkan kembali</span>
                </div>
            </div>
        </section>

        <section id="tentang" class="bg-white py-14 sm:py-16">
            <div class="bw-container">
                <p class="bw-kicker">Manfaat utama</p>
                <div class="mt-2 flex flex-wrap items-end justify-between gap-4">
                    <h2 class="max-w-xl text-3xl font-semibold tracking-tight sm:text-4xl">Informasi titik dan penanganan dalam satu peta.</h2>
                    <p class="max-w-md text-sm leading-6 text-[#6c746e]">Warga bisa melapor melalui browser. Admin memeriksa laporan, petugas memperbarui progres, dan riwayat tetap melekat pada lokasinya.</p>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article class="bw-card p-5"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#CFE8E1] text-lg text-[#1C8C74]">⌖</span><h3 class="mt-4 font-semibold">Peta terbuka</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Cari titik, lihat status penanganan, dan buka riwayat perubahan.</p></article>
                    <article class="bw-card p-5"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#CFE8E1] text-lg text-[#0F3F38]">↻</span><h3 class="mt-4 font-semibold">Riwayat titik</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Laporan, penanganan, dan bukti sebelum-sesudah tersimpan per lokasi.</p></article>
                    <article class="bw-card p-5"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#fff0dc] text-lg text-[#E8871E]">✓</span><h3 class="mt-4 font-semibold">Alur terpantau</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Admin memverifikasi dan menugaskan; petugas memperbarui pekerjaan lapangan.</p></article>
                    <article class="bw-card p-5"><span class="grid h-10 w-10 place-items-center rounded-xl bg-[#f7e9e5] text-lg text-[#a6402b]">＋</span><h3 class="mt-4 font-semibold">Pelaporan ringkas</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Kirim foto, pilih lokasi, lalu pantau status laporan dari akun Anda.</p></article>
                </div>
            </div>
        </section>

        <section class="bw-container py-14 sm:py-16">
            <div class="text-center">
                <p class="bw-kicker">Cara kerja</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight sm:text-4xl">Dari laporan sampai pemantauan ulang</h2>
            </div>
            <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ([['01', 'Warga melaporkan', 'Unggah foto, tandai lokasi, dan jelaskan kondisi sampah.'], ['02', 'Admin memeriksa', 'Admin menilai bukti serta memeriksa titik lama dalam radius 50 meter.'], ['03', 'Petugas menangani', 'Petugas mengunggah foto sebelum dan sesudah serta catatan pekerjaan.'], ['04', 'Riwayat dipantau', 'Jika laporan kembali dekat titik lama, riwayat lokasi tetap tersambung.']] as [$number, $title, $description])
                    <article class="bw-card p-5"><span class="text-2xl font-semibold text-[#0F3F38]">{{ $number }}</span><h3 class="mt-3 font-semibold">{{ $title }}</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">{{ $description }}</p></article>
                @endforeach
            </div>
        </section>

        <section class="bg-[#eeece3] py-14 sm:py-16">
            <div class="bw-container grid gap-8 lg:grid-cols-[.72fr_1.28fr] lg:items-center">
                <div>
                    <p class="bw-kicker">Peta publik</p>
                    <h2 class="mt-2 text-3xl font-semibold tracking-tight">Pantau titik sampah liar di Banjarmasin</h2>
                    <p class="mt-4 text-sm leading-6 text-[#626d65]">Pilih marker untuk membuka ringkasan. Di halaman peta, cari alamat dan saring titik menurut status.</p>
                    <a class="bw-btn bw-btn-primary mt-6" href="{{ route('peta') }}">Buka peta interaktif <span aria-hidden="true">→</span></a>
                </div>
                <div class="overflow-hidden rounded-2xl border border-[#d6d9d0] bg-white shadow-sm">
                    <div id="home-map-detail" data-bw-map-boundaries class="h-[300px]" role="region" aria-label="Peta interaktif Banjarmasin"></div>
                    <div class="flex flex-wrap items-center justify-between gap-3 p-4">
                        <p class="text-sm text-[#647068]">Peta menggunakan OpenStreetMap.</p>
                        <a class="text-sm font-semibold text-[#1C8C74]" href="{{ route('peta') }}">Lihat semua titik →</a>
                    </div>
                </div>
            </div>
        </section>

        <section class="bw-container py-14 sm:py-16">
            <div class="flex flex-wrap items-end justify-between gap-5">
                <div><p class="bw-kicker">Riwayat &amp; bukti</p><h2 class="mt-2 text-3xl font-semibold tracking-tight">Penanganan tercatat, hasilnya bisa dilihat.</h2></div>
                <a class="text-sm font-semibold text-[#1C8C74]" href="{{ route('peta') }}">Jelajahi riwayat titik →</a>
            </div>
            @if ($penangananTerakhir && $penangananTerakhir->foto_sebelum && $penangananTerakhir->foto_sesudah)
                <article class="bw-card mt-7 p-4 sm:p-5">
                    <div class="mb-4 flex flex-wrap items-center justify-between gap-2"><h3 class="text-lg font-semibold">Sebelum dan sesudah penanganan</h3><span class="text-sm text-[#6c746e]">{{ optional($penangananTerakhir->tanggal_penanganan)->format('d M Y') }}</span></div>
                    <div class="grid gap-3 sm:grid-cols-2">
                        <figure class="relative overflow-hidden rounded-xl bg-[#e9ece6]"><img class="h-64 w-full object-cover" src="{{ asset('storage/'.$penangananTerakhir->foto_sebelum) }}" alt="Kondisi titik sebelum penanganan" loading="lazy"><figcaption class="absolute bottom-3 left-3 rounded-full bg-[#a6402b] px-3 py-1 text-xs font-semibold text-white">SEBELUM</figcaption></figure>
                        <figure class="relative overflow-hidden rounded-xl bg-[#e9ece6]"><img class="h-64 w-full object-cover" src="{{ asset('storage/'.$penangananTerakhir->foto_sesudah) }}" alt="Kondisi titik sesudah penanganan" loading="lazy"><figcaption class="absolute bottom-3 left-3 rounded-full bg-[#1C8C74] px-3 py-1 text-xs font-semibold text-white">SESUDAH</figcaption></figure>
                    </div>
                    @if ($penangananTerakhir->catatan)<p class="mt-4 rounded-lg bg-[#CFE8E1] p-3 text-sm leading-6">{{ $penangananTerakhir->catatan }}</p>@endif
                </article>
            @else
                <div class="bw-card mt-7 flex flex-col gap-3 p-6 sm:flex-row sm:items-center"><span class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-[#CFE8E1] text-xl text-[#1C8C74]">↻</span><div><h3 class="font-semibold">Foto penanganan akan tampil di sini</h3><p class="mt-1 text-sm text-[#6c746e]">Setelah petugas mengunggah bukti sebelum dan sesudah, hasilnya tersimpan pada riwayat titik.</p></div></div>
            @endif
        </section>

        <section class="bg-white py-14 sm:py-16">
            <div class="bw-container">
                <div class="text-center"><p class="bw-kicker">Peran pengguna</p><h2 class="mt-2 text-3xl font-semibold tracking-tight">Satu alur untuk warga, admin, dan petugas</h2></div>
                <div class="mt-8 grid gap-4 md:grid-cols-3">
                    <article class="bw-card p-6"><p class="bw-kicker">Warga</p><h3 class="mt-2 text-xl font-semibold">Laporkan dan pantau</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Kirim laporan, lihat status, dan periksa riwayat titik yang pernah ditangani.</p></article>
                    <article class="bw-card p-6"><p class="bw-kicker">Admin</p><h3 class="mt-2 text-xl font-semibold">Verifikasi dan tugaskan</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Periksa bukti dan kedekatan lokasi, catat keputusan, lalu tunjuk petugas.</p></article>
                    <article class="bw-card p-6"><p class="bw-kicker">Petugas</p><h3 class="mt-2 text-xl font-semibold">Tangani dan dokumentasikan</h3><p class="mt-2 text-sm leading-6 text-[#68736b]">Perbarui progres, unggah foto sebelum-sesudah, dan catat volume serta hasil.</p></article>
                </div>
            </div>
        </section>

        <section class="bw-narrow py-14 sm:py-16">
            <div class="text-center"><p class="bw-kicker">Pusat bantuan</p><h2 class="mt-2 text-3xl font-semibold tracking-tight">Pertanyaan umum</h2></div>
            <div class="mt-7 space-y-3">
                <details class="bw-card p-4"><summary class="cursor-pointer font-semibold">Apakah B-WasteMap perlu aplikasi Android?</summary><p class="mt-3 text-sm leading-6 text-[#68736b]">Tidak. B-WasteMap adalah aplikasi web yang bisa dibuka dari browser.</p></details>
                <details class="bw-card p-4"><summary class="cursor-pointer font-semibold">Bagaimana sistem mengenali titik yang berulang?</summary><p class="mt-3 text-sm leading-6 text-[#68736b]">Sistem memberi admin kandidat titik dalam radius 50 meter. Admin memeriksa lokasi dan memilih untuk menyambungkan laporan atau membuat titik baru.</p></details>
                <details class="bw-card p-4"><summary class="cursor-pointer font-semibold">Apakah foto wajib disertakan?</summary><p class="mt-3 text-sm leading-6 text-[#68736b]">Ya. Foto membantu admin menilai laporan dan petugas memahami kondisi lokasi.</p></details>
                <details class="bw-card p-4"><summary class="cursor-pointer font-semibold">Apakah waktu respons dijamin?</summary><p class="mt-3 text-sm leading-6 text-[#68736b]">Aplikasi menampilkan status dan riwayat. Waktu tindak lanjut mengikuti proses operasional petugas.</p></details>
            </div>
        </section>

        <section class="bg-[#0A332D] py-10 text-white sm:py-12">
            <div class="bw-container flex flex-wrap items-center justify-between gap-6">
                <div><p class="text-xs font-semibold uppercase tracking-[.16em] text-[#b7d8c5]">Kota Banjarmasin</p><h2 class="mt-2 max-w-2xl text-2xl font-semibold sm:text-3xl">Mulai pantau titik sampah liar di sekitar Anda.</h2><p class="mt-2 text-sm text-[#d5e4d9]">Lihat peta, kirim laporan, dan ikuti perkembangan penanganan.</p></div>
                <div class="flex flex-wrap gap-3"><a class="bw-btn border border-white/30 bg-white text-[#0A332D]" href="{{ route('peta') }}">Lihat peta</a><a class="bw-btn bg-[#E8871E] text-[#0A332D]" href="{{ route('laporan.create') }}">Laporkan titik</a></div>
            </div>
        </section>
    </main>

    <footer class="bg-[#0d3224] py-5 text-white">
        <div class="bw-container flex flex-wrap items-center justify-between gap-2 text-xs text-[#d4e2d9]"><span>© {{ date('Y') }} B-WasteMap · Kota Banjarmasin</span><span>Petakan, Pantau, dan Tangani</span></div>
    </footer>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const homeMapStyle = { normal: '#1C8C74', selesai: '#1C8C74', perlu_perhatian: '#E8871E', dalam_penanganan: '#0F3F38', berulang: '#a6402b' };
        ['home-map', 'home-map-detail'].forEach((id) => {
            const el = document.getElementById(id);
            if (!el) return;
            const map = L.map(el, { scrollWheelZoom: false }).setView([-3.3194, 114.5908], 12);
            el.__bwMap = map;
            fetch('/api/titik-sampah')
                .then((response) => response.ok ? response.json() : [])
                .then((points) => points.forEach((point) => {
                    const marker = L.circleMarker([point.latitude, point.longitude], {
                        radius: point.status === 'berulang' ? 9 : 7,
                        color: '#fff', weight: 2, fillColor: homeMapStyle[point.status] || '#6c746e', fillOpacity: .95,
                    }).addTo(map);
                    marker.bindPopup(`<strong>Titik #${point.id}</strong><br>${L.Util.escapeHTML(point.alamat || 'Alamat belum dicatat')}<br>${L.Util.escapeHTML(point.status.replaceAll('_', ' '))}<br><a href="${point.url}">Lihat riwayat</a>`);
                }))
                .catch(() => {});
        });
    </script>
</body>
</html>
