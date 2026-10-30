<x-app-layout>
    @section('title', 'Peta publik · B-WasteMap')
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        .leaflet-tooltip.map-boundary-label { background: #fff; border: 0; border-radius: 4px; box-shadow: 0 1px 4px #0a332d30; color: #21392d; font-size: 10px; font-weight: 600; padding: 2px 5px; pointer-events: none; white-space: nowrap; }
        .leaflet-tooltip.map-neighborhood-label { font-size: 9px; font-weight: 500; }
    </style>
@endpush

<main class="pb-10">
    <section class="bg-white py-5">
        <div class="bw-container">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div><p class="bw-kicker">Peta publik</p><h1 class="mt-1 text-2xl font-semibold sm:text-3xl">Titik sampah liar Banjarmasin</h1></div>
                <a class="bw-btn bw-btn-orange" href="{{ route('laporan.create') }}">＋ Buat laporan</a>
            </div>
            <div class="mt-5 grid gap-3 md:grid-cols-2 xl:grid-cols-[minmax(200px,1fr)_210px_210px_auto_auto]">
                <label class="sr-only" for="point-search">Cari titik atau alamat</label>
                <input id="point-search" class="bw-field" type="search" placeholder="Cari nomor titik atau nama jalan…" autocomplete="off">
                <label class="sr-only" for="status-filter">Saring menurut status</label>
                <select id="status-filter" class="bw-field">
                    <option value="">Semua status</option>
                    <option value="normal">Belum ada penanganan aktif</option>
                    <option value="selesai">Selesai ditangani</option>
                    <option value="perlu_perhatian">Menunggu tindak lanjut</option>
                    <option value="dalam_penanganan">Sedang ditangani petugas</option>
                    <option value="berulang">Dilaporkan kembali</option>
                </select>
                <label class="sr-only" for="area-filter">Saring menurut kecamatan</label>
                <select id="area-filter" class="bw-field"><option value="">Semua kecamatan</option></select>
                <button id="locate-me" class="bw-btn bw-btn-outline disabled:cursor-wait disabled:opacity-70" type="button">Lokasi saya</button>
                <button id="reset-filters" class="bw-btn bw-btn-outline" type="button">Atur ulang</button>
            </div>
            <p id="point-count" class="mt-3 text-sm text-[#6c746e]" aria-live="polite">Memuat titik…</p>
            <p id="location-status" class="text-xs text-[#6c746e]" aria-live="polite"></p>
        </div>
    </section>

    <section class="bw-container mt-5">
        <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_300px]">
            <div class="relative overflow-hidden rounded-2xl border border-[#CFE8E1] bg-[#e8e8df]">
                <div id="peta" class="h-[58vh] min-h-[410px] sm:h-[620px]" aria-label="Peta interaktif titik sampah liar"></div>
                <div class="absolute bottom-4 left-4 z-[500] max-w-[calc(100%-2rem)] rounded-xl bg-[#0A332D] p-4 text-white shadow-lg sm:flex sm:items-center sm:gap-5">
                    <div><p class="font-semibold">Menemukan sampah liar?</p><p class="mt-1 text-xs text-white/80">Laporkan lokasi dan foto kondisi.</p></div>
                    <a class="bw-btn bw-btn-orange mt-3 min-h-10 px-3 py-2 text-sm sm:mt-0" href="{{ route('laporan.create') }}">Buat laporan</a>
                </div>
            </div>

            <aside class="space-y-4">
                <section class="bw-card p-5">
                    <h2 class="font-semibold">Status titik</h2>
                    <div class="mt-4 space-y-3 text-sm">
                        <button class="legend-filter flex w-full items-center justify-between text-left" data-status="normal"><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-[#1C8C74]"></i>Belum ada penanganan aktif</span><b id="count-normal">0</b></button>
                        <button class="legend-filter flex w-full items-center justify-between text-left" data-status="selesai"><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-[#1C8C74]"></i>Selesai ditangani</span><b id="count-selesai">0</b></button>
                        <button class="legend-filter flex w-full items-center justify-between text-left" data-status="perlu_perhatian"><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-[#E8871E]"></i>Menunggu tindak lanjut</span><b id="count-perlu_perhatian">0</b></button>
                        <button class="legend-filter flex w-full items-center justify-between text-left" data-status="dalam_penanganan"><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-[#0F3F38]"></i>Sedang ditangani petugas</span><b id="count-dalam_penanganan">0</b></button>
                        <button class="legend-filter flex w-full items-center justify-between text-left" data-status="berulang"><span class="flex items-center gap-2"><i class="h-3 w-3 rounded-full bg-[#a6402b]"></i>Dilaporkan kembali</span><b id="count-berulang">0</b></button>
                    </div>
                    <p class="mt-4 border-t border-[#e5e8e2] pt-3 text-xs leading-5 text-[#7b847d]">Warna menunjukkan kondisi titik saat ini. Riwayat laporan dan penanganan tersimpan terpisah pada detail titik.</p>
                </section>

                <section id="selected-point" class="bw-card hidden p-5" aria-live="polite">
                    <div class="flex items-start justify-between gap-3"><span id="selected-badge" class="map-status"></span><span id="selected-id" class="text-xs text-[#6c746e]"></span></div>
                    <h2 id="selected-address" class="mt-4 text-lg font-semibold"></h2>
                    <p id="selected-area" class="mt-1 text-sm text-[#6c746e]"></p>
                    <div class="mt-4 grid grid-cols-2 gap-3 border-y border-[#e5e8e2] py-3 text-sm">
                        <div><strong id="selected-reports" class="block text-lg"></strong><span class="text-xs text-[#6c746e]">Laporan</span></div>
                        <div><strong id="selected-handled" class="block text-lg"></strong><span class="text-xs text-[#6c746e]">Penanganan</span></div>
                    </div>
                    <a id="selected-link" class="bw-btn bw-btn-primary mt-4 w-full" href="#">Lihat detail titik →</a>
                </section>

                <section class="rounded-2xl bg-[#CFE8E1] p-5">
                    <p class="bw-kicker">Kota Banjarmasin</p>
                    <p class="mt-2 text-sm leading-6 text-[#40574a]">Warna kelurahan mengikuti kecamatannya. Buka kontrol layer di pojok kanan peta untuk menampilkan batas kecamatan, kelurahan, atau kota.</p>
                    <p class="mt-2 text-xs leading-5 text-[#52695d]">Setiap laporan diperiksa admin. Titik yang berdekatan menjadi kandidat; keputusan penggabungan tetap dicatat.</p>
                </section>
            </aside>
        </div>
    </section>
</main>
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
    const map = L.map('peta').setView([-3.3194, 114.5908], 12);
    const baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors · Batas wilayah &copy; <a href="https://github.com/cahyadsn/wilayah_boundaries/tree/26fce16d15cc1dc53da79a79359373130cae485d">Cahya DSN</a> (MIT)',
    }).addTo(map);
    map.createPane('neighborhoodBoundaryPane');
    map.getPane('neighborhoodBoundaryPane').style.zIndex = 350;
    map.createPane('districtBoundaryPane');
    map.getPane('districtBoundaryPane').style.zIndex = 360;
    map.createPane('cityBoundaryPane');
    map.getPane('cityBoundaryPane').style.zIndex = 370;
    const areaColors = {
        'Banjarmasin Utara': '#4caf27',
        'Banjarmasin Selatan': '#ff8a16',
        'Banjarmasin Timur': '#92ca17',
        'Banjarmasin Barat': '#f1df18',
        'Banjarmasin Tengah': '#ed3030',
    };
    const districtBoundaries = L.geoJSON(null, {
        pane: 'districtBoundaryPane',
        style: (feature) => ({
            color: '#315b49', weight: 1.25, opacity: .95,
            fill: false,
            lineCap: 'round', lineJoin: 'round',
        }),
        onEachFeature: (feature, layer) => {
            const label = document.createElement('span');
            label.textContent = feature.properties.nama;
            layer.bindTooltip(label, { permanent: true, direction: 'center', className: 'map-boundary-label' });
            const popup = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = feature.properties.nama;
            const detail = document.createElement('div');
            detail.textContent = `Kecamatan · kode ${feature.properties.kode}`;
            popup.append(title, detail);
            layer.bindPopup(popup);
        },
    });
    const neighborhoodBoundaries = L.geoJSON(null, {
        pane: 'neighborhoodBoundaryPane',
        style: (feature) => ({
            color: '#536558', weight: .55, opacity: .9,
            fillColor: areaColors[feature.properties.kecamatan] || '#85a786', fillOpacity: .52,
        }),
        onEachFeature: (feature, layer) => {
            const label = document.createElement('span');
            label.textContent = feature.properties.nama;
            layer.bindTooltip(label, { permanent: true, direction: 'center', className: 'map-boundary-label map-neighborhood-label' });
            const popup = document.createElement('div');
            const title = document.createElement('strong');
            title.textContent = feature.properties.nama;
            const detail = document.createElement('div');
            detail.textContent = `${feature.properties.kecamatan} · kode ${feature.properties.kode}`;
            popup.append(title, detail);
            layer.bindPopup(popup);
        },
    });
    const cityBoundary = L.geoJSON(null, {
        pane: 'cityBoundaryPane',
        style: { color: '#c62828', weight: 2.5, opacity: .95, fill: false, lineCap: 'round', lineJoin: 'round' },
        onEachFeature: (feature, layer) => {
            const popup = document.createElement('strong');
            popup.textContent = feature.properties.nama;
            layer.bindPopup(popup);
        },
    });
    districtBoundaries.addTo(map);
    neighborhoodBoundaries.addTo(map);
    cityBoundary.addTo(map);
    L.control.layers(
        { 'OpenStreetMap': baseLayer },
        { 'Warna & batas kelurahan': neighborhoodBoundaries, 'Batas kecamatan': districtBoundaries, 'Batas Kota Banjarmasin': cityBoundary },
        { collapsed: true }
    ).addTo(map);

    function updateBoundaryLabels() {
        const zoom = map.getZoom();
        districtBoundaries.eachLayer((layer) => {
            if (map.hasLayer(layer) && zoom >= 12) {
                const center = layer.feature.properties.titik_tengah;
                layer.openTooltip(center ? L.latLng(center[1], center[0]) : undefined);
            } else {
                layer.closeTooltip();
            }
        });
        neighborhoodBoundaries.eachLayer((layer) => {
            if (map.hasLayer(layer)) zoom >= 14 ? layer.openTooltip() : layer.closeTooltip();
        });
    }

    Promise.all([
        fetch('/data/wilayah-kecamatan-batas.geojson'),
        fetch('/data/wilayah-banjarmasin.geojson'),
        fetch('/data/wilayah-kota-batas.geojson'),
    ])
        .then((responses) => {
            if (responses.some((response) => !response.ok)) throw new Error('Batas wilayah tidak tersedia.');
            return Promise.all(responses.map((response) => response.json()));
        })
        .then(([districtData, neighborhoodData, cityData]) => {
            districtBoundaries.addData(districtData);
            neighborhoodBoundaries.addData({ type: 'FeatureCollection', features: neighborhoodData.features.filter((feature) => feature.properties.tingkat === 'Kelurahan') });
            cityBoundary.addData(cityData);
            districtBounds = districtBoundaries.getBounds();
            updateBoundaryLabels();
            fitMapToAreasAndPoints();
        })
        .catch(() => { locationStatus.textContent = 'Batas wilayah belum dapat dimuat.'; });

    map.on('zoomend overlayadd', updateBoundaryLabels);
    const palette = { normal: '#1C8C74', selesai: '#1C8C74', perlu_perhatian: '#E8871E', dalam_penanganan: '#0F3F38', berulang: '#a6402b' };
    const labels = { normal: 'Belum ada penanganan aktif', selesai: 'Selesai ditangani', perlu_perhatian: 'Menunggu tindak lanjut', dalam_penanganan: 'Sedang ditangani petugas', berulang: 'Dilaporkan kembali' };
    const layers = [];
    const search = document.getElementById('point-search');
    const statusFilter = document.getElementById('status-filter');
    const areaFilter = document.getElementById('area-filter');
    Object.keys(areaColors).sort((first, second) => first.localeCompare(second, 'id'))
        .forEach((area) => areaFilter.add(new Option(area, area)));
    const pointCount = document.getElementById('point-count');
    const locationStatus = document.getElementById('location-status');
    const locateButton = document.getElementById('locate-me');
    const selected = document.getElementById('selected-point');
    let points = [];
    let districtBounds = null;
    let currentLocation = null;
    let userMarker = null;
    let userAccuracy = null;

    function fitMapToAreasAndPoints() {
        if (currentLocation) return;
        const bounds = L.latLngBounds([]);
        if (districtBounds?.isValid()) bounds.extend(districtBounds);
        points.forEach((point) => bounds.extend([point.latitude, point.longitude]));
        if (bounds.isValid()) map.fitBounds(bounds.pad(.08), { maxZoom: 13 });
    }

    function updateSelected(point) {
        selected.classList.remove('hidden');
        const badge = document.getElementById('selected-badge');
        badge.textContent = labels[point.status] || point.status;
        badge.style.color = palette[point.status] || '#6c746e';
        badge.style.backgroundColor = `${palette[point.status] || '#6c746e'}18`;
        document.getElementById('selected-id').textContent = `TITIK #${point.id}`;
        document.getElementById('selected-address').textContent = point.alamat || 'Alamat belum dicatat';
        document.getElementById('selected-area').textContent = point.kecamatan || 'Kota Banjarmasin';
        document.getElementById('selected-reports').textContent = point.total_laporan;
        document.getElementById('selected-handled').textContent = point.total_penanganan;
        document.getElementById('selected-link').href = point.url;
    }

    function applyFilters() {
        const query = search.value.trim().toLocaleLowerCase('id');
        const status = statusFilter.value;
        const area = areaFilter.value;
        let shown = 0;

        layers.forEach(({ point, marker }) => {
            const text = `${point.id} ${point.alamat || ''} ${point.kecamatan || ''}`.toLocaleLowerCase('id');
            const visible = (!query || text.includes(query)) && (!status || point.status === status) && (!area || point.kecamatan === area);
            if (visible) { marker.addTo(map); shown++; } else { marker.remove(); }
        });

        pointCount.textContent = `Menampilkan ${shown} dari ${points.length} titik`;
    }

    search.addEventListener('input', applyFilters);
    statusFilter.addEventListener('change', applyFilters);
    areaFilter.addEventListener('change', applyFilters);
    document.getElementById('reset-filters').addEventListener('click', () => {
        search.value = '';
        statusFilter.value = '';
        areaFilter.value = '';
        applyFilters();
    });
    locateButton.addEventListener('click', () => {
        if (!window.isSecureContext) {
            locationStatus.textContent = 'GPS memerlukan HTTPS atau alamat localhost. Geser peta untuk mencari lokasi.';
            return;
        }
        if (!navigator.geolocation) {
            locationStatus.textContent = 'Perangkat ini tidak menyediakan GPS. Geser peta untuk mencari lokasi.';
            return;
        }

        locateButton.disabled = true;
        locateButton.textContent = 'Mencari lokasi…';
        locationStatus.textContent = 'Menunggu izin lokasi dari browser…';
        navigator.geolocation.getCurrentPosition(
            ({ coords }) => {
                currentLocation = L.latLng(coords.latitude, coords.longitude);
                if (userMarker) map.removeLayer(userMarker);
                if (userAccuracy) map.removeLayer(userAccuracy);
                userAccuracy = L.circle(currentLocation, {
                    radius: coords.accuracy,
                    color: '#286d80',
                    fillColor: '#286d80',
                    fillOpacity: .12,
                    weight: 1,
                }).addTo(map);
                userMarker = L.circleMarker(currentLocation, {
                    radius: 8,
                    color: '#fff',
                    weight: 3,
                    fillColor: '#286d80',
                    fillOpacity: 1,
                }).addTo(map).bindPopup('Lokasi perangkat Anda');
                map.setView(currentLocation, Math.max(map.getZoom(), 16));
                locationStatus.textContent = `Lokasi ditemukan · akurasi ±${Math.round(coords.accuracy)} m.`;
                locateButton.disabled = false;
                locateButton.textContent = 'Lokasi saya';
            },
            (error) => {
                locationStatus.textContent = {
                    1: 'Izin lokasi ditolak. Izinkan akses lokasi di browser, lalu coba lagi.',
                    2: 'Sinyal lokasi tidak tersedia. Pastikan GPS aktif dan coba lagi.',
                    3: 'Pencarian lokasi melewati batas waktu. Coba lagi.',
                }[error.code] || 'Lokasi tidak tersedia. Coba lagi atau geser peta.';
                locateButton.disabled = false;
                locateButton.textContent = 'Coba GPS lagi';
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 }
        );
    });

    document.querySelectorAll('.legend-filter').forEach((button) => button.addEventListener('click', () => {
        statusFilter.value = statusFilter.value === button.dataset.status ? '' : button.dataset.status;
        applyFilters();
    }));

    fetch('/api/titik-sampah')
        .then((response) => { if (!response.ok) throw new Error('Data peta tidak tersedia.'); return response.json(); })
        .then((data) => {
            points = data;
            const areas = [...new Set([...Object.keys(areaColors), ...points.map((point) => point.kecamatan).filter(Boolean)])]
                .sort((a, b) => a.localeCompare(b, 'id'));
            areas.filter((area) => !Object.hasOwn(areaColors, area)).forEach((area) => areaFilter.add(new Option(area, area)));

            const counts = { normal: 0, selesai: 0, perlu_perhatian: 0, dalam_penanganan: 0, berulang: 0 };
            points.forEach((point) => {
                counts[point.status] = (counts[point.status] || 0) + 1;
                const marker = L.circleMarker([point.latitude, point.longitude], {
                    radius: point.status === 'berulang' ? 10 : 8,
                    color: '#fff', weight: 2, fillColor: palette[point.status] || '#6c746e', fillOpacity: .95,
                });
                marker.bindPopup(`<strong>Titik #${point.id}</strong><br>${L.Util.escapeHTML(point.alamat || 'Alamat belum dicatat')}<br>${L.Util.escapeHTML(labels[point.status] || point.status)}`);
                marker.on('click', () => updateSelected(point));
                layers.push({ point, marker });
            });
            Object.entries(counts).forEach(([status, count]) => {
                const element = document.getElementById(`count-${status}`);
                if (element) element.textContent = count;
            });
            applyFilters();

            fitMapToAreasAndPoints();
        })
        .catch(() => { pointCount.textContent = 'Titik belum bisa dimuat. Coba segarkan halaman.'; });
})();
</script>
@endpush
</x-app-layout>
