<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Peta Titik Sampah Liar — Kota Banjarmasin
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-6xl mx-auto p-4">
            <p class="mb-4 text-sm">
                <span class="text-green-600">● Normal</span> ·
                <span class="text-yellow-600">● Perlu Perhatian</span> ·
                <span class="text-red-600">● Titik Berulang</span>
            </p>
            <div id="peta" style="height: 600px;"></div>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('peta').setView([-3.3194, 114.5908], 12); // pusat Kota Banjarmasin

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        const warna = { normal: 'green', perlu_perhatian: 'orange', berulang: 'red' };

        fetch("{{ route('titik-sampah.data') }}")
            .then(res => res.json())
            .then(data => data.forEach(titik => {
                L.circleMarker([titik.latitude, titik.longitude], {
                    radius: 8, weight: 1, color: '#333',
                    fillColor: warna[titik.status] ?? 'gray', fillOpacity: 0.85,
                })
                .bindPopup(`<b>${titik.alamat ?? 'Titik #' + titik.id}</b><br>Status: ${titik.status}<br>Total laporan: ${titik.total_laporan}<br><a href="/titik/${titik.id}">Lihat riwayat →</a>`)
                .addTo(map);
            }));
    </script>
</x-app-layout>