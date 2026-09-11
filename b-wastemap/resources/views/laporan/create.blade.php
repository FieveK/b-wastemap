<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporkan Titik Sampah Liar
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <p class="text-sm mb-2 text-gray-600">Klik pada peta untuk menandai lokasi temuan sampah.</p>
            <div id="peta-form" style="height: 400px;" class="mb-4 rounded overflow-hidden border"></div>
            <p id="koordinat-info" class="text-xs text-gray-500 mb-4">Belum ada titik dipilih.</p>

            <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="space-y-4 bg-white p-4 rounded shadow">
                @csrf
                <input type="hidden" name="latitude" id="latitude">
                <input type="hidden" name="longitude" id="longitude">

                <div>
                    <x-input-label for="foto" value="Foto Sampah" />
                    <input id="foto" type="file" name="foto" accept="image/*" class="block mt-1 w-full text-sm" required>
                    <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="jenis_sampah" value="Jenis Sampah" />
                    <select id="jenis_sampah" name="jenis_sampah" class="border rounded p-2 w-full mt-1" required>
                        <option value="rumah_tangga">Sampah Rumah Tangga</option>
                        <option value="bangunan">Puing Bangunan</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                    <x-input-error :messages="$errors->get('jenis_sampah')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="deskripsi" value="Deskripsi" />
                    <textarea id="deskripsi" name="deskripsi" class="border rounded p-2 w-full mt-1" rows="3">{{ old('deskripsi') }}</textarea>
                    <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                </div>

                <x-input-error :messages="$errors->get('latitude')" class="mt-2" />

                <x-primary-button>Kirim Laporan</x-primary-button>
            </form>
        </div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const map = L.map('peta-form').setView([-3.3194, 114.5908], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors'
        }).addTo(map);

        let marker;
        map.on('click', function (e) {
            document.getElementById('latitude').value = e.latlng.lat;
            document.getElementById('longitude').value = e.latlng.lng;
            document.getElementById('koordinat-info').innerText =
                'Lokasi dipilih: ' + e.latlng.lat.toFixed(6) + ', ' + e.latlng.lng.toFixed(6);
            if (marker) map.removeLayer(marker);
            marker = L.marker(e.latlng).addTo(map);
        });
    </script>
</x-app-layout>