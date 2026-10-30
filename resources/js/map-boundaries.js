const areaColors = {
    'Banjarmasin Utara': '#4caf27',
    'Banjarmasin Selatan': '#ff8a16',
    'Banjarmasin Timur': '#92ca17',
    'Banjarmasin Barat': '#f1df18',
    'Banjarmasin Tengah': '#ed3030',
};

export function addBanjarmasinBoundaries(map) {
    const L = window.L;
    const baseLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors · Batas wilayah &copy; <a href="https://github.com/cahyadsn/wilayah_boundaries/tree/26fce16d15cc1dc53da79a79359373130cae485d">Cahya DSN</a> (MIT)',
    }).addTo(map);

    map.createPane('neighborhoodBoundaryPane');
    map.getPane('neighborhoodBoundaryPane').style.zIndex = 350;
    map.createPane('districtBoundaryPane');
    map.getPane('districtBoundaryPane').style.zIndex = 360;
    map.createPane('cityBoundaryPane');
    map.getPane('cityBoundaryPane').style.zIndex = 370;

    const districts = L.geoJSON(null, {
        pane: 'districtBoundaryPane',
        style: { color: '#315b49', weight: 1.25, opacity: .95, fill: false, lineCap: 'round', lineJoin: 'round' },
        onEachFeature: (feature, layer) => {
            const label = document.createElement('span');
            label.textContent = feature.properties.nama;
            layer.bindTooltip(label, { permanent: true, direction: 'center', className: 'map-boundary-label' });
            const detail = document.createElement('div');
            detail.textContent = `Kecamatan · kode ${feature.properties.kode}`;
            const title = document.createElement('strong');
            title.textContent = feature.properties.nama;
            const popup = document.createElement('div');
            popup.append(title, detail);
            layer.bindPopup(popup);
        },
    }).addTo(map);

    const neighborhoods = L.geoJSON(null, {
        pane: 'neighborhoodBoundaryPane',
        style: (feature) => ({
            color: '#536558', weight: .55, opacity: .9,
            fillColor: areaColors[feature.properties.kecamatan] || '#85a786', fillOpacity: .52,
        }),
        onEachFeature: (feature, layer) => {
            const label = document.createElement('span');
            label.textContent = feature.properties.nama;
            layer.bindTooltip(label, { permanent: true, direction: 'center', className: 'map-boundary-label map-neighborhood-label' });
            const detail = document.createElement('div');
            detail.textContent = `${feature.properties.kecamatan} · kode ${feature.properties.kode}`;
            const title = document.createElement('strong');
            title.textContent = feature.properties.nama;
            const popup = document.createElement('div');
            popup.append(title, detail);
            layer.bindPopup(popup);
        },
    }).addTo(map);

    const city = L.geoJSON(null, {
        pane: 'cityBoundaryPane',
        style: { color: '#c62828', weight: 2.5, opacity: .95, fill: false, lineCap: 'round', lineJoin: 'round' },
        onEachFeature: (feature, layer) => {
            const title = document.createElement('strong');
            title.textContent = feature.properties.nama;
            layer.bindPopup(title);
        },
    }).addTo(map);

    L.control.layers(
        { OpenStreetMap: baseLayer },
        { 'Warna & batas kelurahan': neighborhoods, 'Batas kecamatan': districts, 'Batas Kota Banjarmasin': city },
        { collapsed: true },
    ).addTo(map);

    const updateLabels = () => {
        const zoom = map.getZoom();
        districts.eachLayer((layer) => {
            if (map.hasLayer(districts) && zoom >= 12) {
                const center = layer.feature.properties.titik_tengah;
                layer.openTooltip(center ? L.latLng(center[1], center[0]) : undefined);
            } else {
                layer.closeTooltip();
            }
        });
        neighborhoods.eachLayer((layer) => {
            if (map.hasLayer(neighborhoods) && zoom >= 14) layer.openTooltip();
            else layer.closeTooltip();
        });
    };
    map.on('zoomend overlayadd', updateLabels);

    Promise.all([
        fetch('/data/wilayah-kecamatan-batas.geojson').then((response) => response.json()),
        fetch('/data/wilayah-banjarmasin.geojson').then((response) => response.json()),
        fetch('/data/wilayah-kota-batas.geojson').then((response) => response.json()),
    ])
        .then(([districtData, neighborhoodData, cityData]) => {
            districts.addData(districtData);
            neighborhoods.addData({
                type: 'FeatureCollection',
                features: neighborhoodData.features.filter((feature) => feature.properties.tingkat === 'Kelurahan'),
            });
            city.addData(cityData);
            updateLabels();
        })
        .catch((error) => console.error('B-WasteMap gagal memuat batas wilayah.', error));
}
