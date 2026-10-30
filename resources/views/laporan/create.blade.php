<x-app-layout>
    @section('title', 'Buat laporan · B-WasteMap')
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    @endpush

    <main class="min-h-[calc(100vh-150px)] py-8 sm:py-12">
        <div class="bw-container max-w-5xl">
            <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
                <div><p class="bw-kicker">Pelaporan warga</p><h1 class="mt-1 text-2xl font-semibold sm:text-3xl">Laporkan titik sampah</h1></div>
                <a class="text-sm font-medium text-[#1C8C74]" href="{{ route('laporan.lacak') }}">Lacak token laporan →</a>
            </div>

            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                    <p class="font-semibold">Laporan belum terkirim.</p>
                    <p class="mt-1">Periksa kembali foto dan informasi lokasi serta kondisi.</p>
                </div>
            @endif

            <form id="report-form" method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="bw-card overflow-hidden">
                @csrf
                <div class="grid lg:grid-cols-[270px_minmax(0,1fr)]">
                    <aside class="border-b border-[#e4e8e0] bg-[#f1f0e8] p-5 lg:border-b-0 lg:border-r lg:p-7">
                        <ol class="space-y-5" aria-label="Tahapan laporan">
                            <li class="wizard-step-label is-active" data-step-label="1"><span>1</span><div><strong>Unggah foto</strong><small>Bukti kondisi lokasi</small></div></li>
                            <li class="wizard-step-label" data-step-label="2"><span>2</span><div><strong>Pilih lokasi</strong><small>Tandai pin pada peta</small></div></li>
                            <li class="wizard-step-label" data-step-label="3"><span>3</span><div><strong>Isi keterangan</strong><small>Kondisi dan jenis sampah</small></div></li>
                        </ol>
                        <div class="mt-7 rounded-xl bg-[#fff0dc] p-4 text-sm leading-6 text-[#684a2a]">
                            <strong class="block text-[#8a4b00]">Jaga keselamatan</strong>
                            Jangan mendekati tumpukan yang berbahaya. Hindari menyertakan wajah dan pelat nomor pada foto.
                        </div>
                    </aside>

                    <div class="p-5 sm:p-8">
                        <section class="wizard-panel" data-wizard-step="1" aria-labelledby="step-one-title">
                            <p class="bw-kicker">Langkah 1 dari 3</p>
                            <h2 id="step-one-title" tabindex="-1" class="mt-2 text-2xl font-semibold">Unggah foto lokasi</h2>
                            <p class="mt-2 max-w-2xl text-sm leading-6 text-[#6c746e]">Kirim laporan tanpa login. Ambil foto ber-GPS dengan Open Camera atau kamera website; simpan token progres setelah laporan terkirim.</p>
                            <div class="mt-4 flex flex-col gap-4 rounded-xl border border-[#cfe8e1] bg-[#f2f8f5] p-4 text-sm leading-6 text-[#40564b] sm:flex-row sm:items-center">
                                <img class="h-16 w-16 shrink-0 rounded-2xl" src="{{ asset('images/open-camera-icon.png') }}" alt="Ikon aplikasi Open Camera" width="64" height="64">
                                <div>
                                    <strong class="block text-[#154734]">Open Camera untuk foto bergeotag</strong>
                                    <p>Izinkan akses lokasi, lalu aktifkan penyimpanan lokasi dan stempel lokasi pada foto. Unggah foto JPG asli agar metadata GPS tetap terbaca. Kamera website di bawah memakai izin kamera dan lokasi dari browser; itu bukan antarmuka aplikasi Open Camera.</p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <a class="inline-flex min-h-10 items-center rounded-lg bg-[#0A332D] px-3 py-2 text-sm font-semibold text-white hover:bg-[#154734]" href="https://play.google.com/store/apps/details?id=net.sourceforge.opencamera" target="_blank" rel="noopener noreferrer">
                                            Unduh di Google Play <span class="sr-only">(terbuka di tab baru)</span>
                                        </a>
                                    </div>
                                    <details class="mt-3">
                                        <summary class="w-fit cursor-pointer font-semibold text-[#11684f] underline underline-offset-2">Tonton tutorial Open Camera di halaman ini</summary>
                                        <div class="mt-3">
                                            <iframe class="aspect-[9/16] w-full max-w-[240px] rounded-xl border border-[#cfe8e1] bg-black" src="https://www.youtube-nocookie.com/embed/kVb6X2c7Smg" title="Tutorial Open Camera untuk foto bergeotag" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                                            <p class="mt-2 text-xs text-[#6c746e]">Video disediakan YouTube. Jika pemutaran dibatasi, <a class="font-semibold underline" href="https://www.youtube.com/shorts/kVb6X2c7Smg" target="_blank" rel="noopener noreferrer">buka tutorial di YouTube</a>.</p>
                                        </div>
                                    </details>
                                </div>
                            </div>
                            <div class="mt-5 rounded-xl border border-[#dce4da] bg-white p-4">
                                <div class="flex flex-wrap items-center justify-between gap-3">
                                    <div>
                                        <strong class="block text-[#154734]">Atau ambil foto langsung di website</strong>
                                        <p class="mt-1 text-sm leading-6 text-[#6c746e]">Kamera dan lokasi dibuka setelah tombol ditekan. Browser menempelkan koordinat pada foto dan metadata EXIF GPS.</p>
                                    </div>
                                    <button id="start-browser-camera" class="bw-btn bw-btn-outline" type="button">Buka kamera website</button>
                                </div>
                                <div id="browser-camera-panel" class="mt-4 hidden" hidden>
                                    <video id="browser-camera-preview" class="max-h-[65vh] w-full rounded-xl bg-black object-contain" autoplay playsinline muted aria-label="Pratinjau kamera belakang"></video>
                                    <p id="browser-camera-status" class="mt-3 text-sm text-[#58665c]" role="status" aria-live="polite"></p>
                                    <div class="mt-3 flex flex-wrap gap-2">
                                        <button id="capture-browser-photo" class="bw-btn bw-btn-primary" type="button" disabled>Ambil foto</button>
                                        <button id="stop-browser-camera" class="bw-btn bw-btn-outline" type="button">Matikan kamera</button>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 rounded-2xl border-2 border-dashed border-[#91b39d] bg-[#fbfcfa] p-6 text-center sm:p-10">
                                <div id="photo-empty">
                                    <span class="mx-auto grid h-14 w-14 place-items-center rounded-full bg-[#CFE8E1] text-2xl text-[#1C8C74]" aria-hidden="true">◎</span>
                                    <p class="mt-4 font-medium">Pilih foto dari perangkat</p>
                                    <p class="mt-1 text-sm text-[#6c746e]">JPG/JPEG dengan metadata GPS · Maksimal 10 MB · 1 foto</p>
                                </div>
                                <img id="photo-preview" class="mx-auto hidden max-h-64 rounded-xl object-contain" alt="Pratinjau foto laporan">
                                <label for="foto" class="bw-btn bw-btn-outline mt-5 cursor-pointer">Pilih foto</label>
                                <input id="foto" class="sr-only" type="file" name="foto" accept="image/jpeg" required>
                                <p id="geotag-scan-status" class="mt-3 text-sm text-[#58665c]" role="status" aria-live="polite"></p>
                                <p id="photo-error" class="mt-3 text-sm text-red-700" role="alert"></p>
                                <x-input-error :messages="$errors->get('foto')" class="mt-2" />
                            </div>
                            <label class="mt-4 flex items-start gap-3 rounded-xl bg-[#f2f4ef] p-4 text-sm leading-6">
                                <input id="geotag-visible-confirm" class="mt-1 rounded border-gray-300 text-[#1C8C74] focus:ring-[#1C8C74]" type="checkbox" name="konfirmasi_geotag_visual" value="1" required>
                                <span>Saya sudah melihat foto dan memastikan tulisan lokasi/koordinat terlihat pada gambar serta sesuai dengan titik peta.</span>
                            </label>
                            <div class="mt-5 flex justify-end"><button id="next-photo" class="bw-btn bw-btn-primary" type="button">Lanjut: pilih lokasi →</button></div>
                        </section>

                        <section class="wizard-panel hidden" data-wizard-step="2" aria-labelledby="step-two-title" hidden>
                            <p class="bw-kicker">Langkah 2 dari 3</p>
                            <h2 id="step-two-title" tabindex="-1" class="mt-2 text-2xl font-semibold">Konfirmasi lokasi</h2>
                            <p class="mt-2 text-sm leading-6 text-[#6c746e]">Koordinat dibaca dari EXIF. Wilayah ditentukan dari batas kelurahan/kecamatan; sistem mencari titik terdekat dalam radius 50 meter. Periksa pin dan akurasi GPS.</p>
                            <p id="coordinate-label" class="mt-5 text-sm text-[#58665c]" aria-live="polite">Pilih foto dengan metadata GPS terlebih dahulu.</p>
                            <div id="report-map" data-bw-map-boundaries class="mt-4 h-[360px] overflow-hidden rounded-xl border border-[#CFE8E1]" aria-label="Pratinjau lokasi dari metadata foto"></div>
                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                <div><label class="bw-label" for="alamat">Alamat dari koordinat <span class="font-normal text-[#7b847d]">(opsional, bisa diedit)</span></label><input id="alamat" class="bw-field" type="text" name="alamat" maxlength="150" value="{{ old('alamat') }}" placeholder="Alamat akan dicari dari titik GPS"></div>
                                <div><label class="bw-label" for="wilayah-terdeteksi">Kelurahan dan kecamatan</label><input id="wilayah-terdeteksi" class="bw-field bg-[#f1f4ef]" type="text" value="" placeholder="Terisi otomatis dari titik koordinat" readonly><p class="mt-1 text-xs text-[#6c746e]">Wilayah tidak dipilih manual.</p></div>
                            </div>
                            <input id="latitude" type="hidden" name="latitude" value="{{ old('latitude') }}" required>
                            <input id="longitude" type="hidden" name="longitude" value="{{ old('longitude') }}" required>
                            <x-input-error :messages="$errors->get('latitude')" class="mt-2" />
                            <div class="mt-5 flex justify-between gap-3"><button class="bw-btn bw-btn-outline" data-back="1" type="button">← Kembali</button><button id="next-location" class="bw-btn bw-btn-primary" type="button">Lanjut: isi keterangan →</button></div>
                        </section>

                        <section class="wizard-panel hidden" data-wizard-step="3" aria-labelledby="step-three-title" hidden>
                            <p class="bw-kicker">Langkah 3 dari 3</p>
                            <h2 id="step-three-title" tabindex="-1" class="mt-2 text-2xl font-semibold">Lengkapi keterangan</h2>
                            <p class="mt-2 text-sm leading-6 text-[#6c746e]">Informasi singkat membantu admin memeriksa dan memprioritaskan laporan.</p>
                            <div class="mt-6 grid gap-5 sm:grid-cols-2">
                                <div>
                                    <label class="bw-label" for="jenis_sampah">Jenis sampah</label>
                                    <select id="jenis_sampah" name="jenis_sampah" class="bw-field" required>
                                        <option value="">Pilih jenis</option>
                                        <option value="rumah_tangga" @selected(old('jenis_sampah') === 'rumah_tangga')>Sampah rumah tangga</option>
                                        <option value="bangunan" @selected(old('jenis_sampah') === 'bangunan')>Puing bangunan</option>
                                        <option value="lainnya" @selected(old('jenis_sampah') === 'lainnya')>Lainnya</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('jenis_sampah')" class="mt-2" />
                                </div>
                                <div>
                                    <label class="bw-label" for="perkiraan_kondisi">Kondisi lokasi</label>
                                    <select id="perkiraan_kondisi" name="perkiraan_kondisi" class="bw-field" required>
                                        <option value="">Pilih kondisi</option>
                                        <option @selected(old('perkiraan_kondisi') === 'menumpuk') value="menumpuk">Sampah menumpuk</option>
                                        <option @selected(old('perkiraan_kondisi') === 'mengganggu_akses') value="mengganggu_akses">Mengganggu akses jalan</option>
                                        <option @selected(old('perkiraan_kondisi') === 'dekat_saluran') value="dekat_saluran">Dekat saluran atau aliran air</option>
                                        <option @selected(old('perkiraan_kondisi') === 'lainnya') value="lainnya">Kondisi lainnya</option>
                                    </select>
                                    <x-input-error :messages="$errors->get('perkiraan_kondisi')" class="mt-2" />
                                </div>
                            </div>
                            <div class="mt-5">
                                <label class="bw-label" for="potensi_bahaya">Apakah lokasi memiliki potensi bahaya?</label>
                                <select id="potensi_bahaya" name="potensi_bahaya" class="bw-field" required>
                                    <option value="tidak" @selected(old('potensi_bahaya', 'tidak') === 'tidak')>Tidak</option>
                                    <option value="menghalangi_jalan" @selected(old('potensi_bahaya') === 'menghalangi_jalan')>Menghalangi jalan</option>
                                    <option value="dekat_aliran_air" @selected(old('potensi_bahaya') === 'dekat_aliran_air')>Dekat aliran air</option>
                                    <option value="keduanya" @selected(old('potensi_bahaya') === 'keduanya')>Menghalangi jalan dan dekat aliran air</option>
                                </select>
                            </div>
                            <div class="mt-5">
                                <label class="bw-label" for="deskripsi">Deskripsi tambahan <span class="font-normal text-[#7b847d]">(opsional)</span></label>
                                <textarea id="deskripsi" name="deskripsi" class="bw-field" rows="4" maxlength="300" placeholder="Jelaskan patokan lokasi atau kondisi yang perlu diketahui admin.">{{ old('deskripsi') }}</textarea>
                                <p class="mt-1 text-right text-xs text-[#7b847d]"><span id="description-length">{{ strlen(old('deskripsi', '')) }}</span>/300</p>
                                <x-input-error :messages="$errors->get('deskripsi')" class="mt-2" />
                            </div>
                            <label class="mt-5 flex items-start gap-3 rounded-xl bg-[#f2f4ef] p-4 text-sm leading-6"><input class="mt-1 rounded border-gray-300 text-[#1C8C74] focus:ring-[#1C8C74]" type="checkbox" required><span>Saya memastikan informasi sesuai kondisi yang saya lihat di lapangan.</span></label>
                            <div class="mt-6 flex flex-wrap justify-between gap-3"><button class="bw-btn bw-btn-outline" data-back="2" type="button">← Kembali</button><button class="bw-btn bw-btn-primary" type="submit">Kirim laporan</button></div>
                        </section>
                    </div>
                </div>
            </form>
            <p class="mt-4 text-center text-xs text-[#79827a]">Informasi lokasi digunakan untuk memeriksa dan menangani laporan.</p>
        </div>
    </main>

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
        (() => {
            const panels = [...document.querySelectorAll('[data-wizard-step]')];
            const labels = [...document.querySelectorAll('[data-step-label]')];
            const fileInput = document.getElementById('foto');
            const photoPreview = document.getElementById('photo-preview');
            const photoEmpty = document.getElementById('photo-empty');
            const photoError = document.getElementById('photo-error');
            const geotagStatus = document.getElementById('geotag-scan-status');
            const geotagVisibleConfirm = document.getElementById('geotag-visible-confirm');
            const latitude = document.getElementById('latitude');
            const longitude = document.getElementById('longitude');
            const coordinateLabel = document.getElementById('coordinate-label');
            const areaLabel = document.getElementById('wilayah-terdeteksi');
            const addressInput = document.getElementById('alamat');
            let step = 1;
            let map;
            let marker;
            let radiusCircle;
            let nearestMarker;
            let scanGeneration = 0;
            let photoMetadataReady = false;
            let previewUrl = null;

            function showStep(nextStep) {
                step = nextStep;
                panels.forEach((panel) => {
                    const active = Number(panel.dataset.wizardStep) === step;
                    panel.hidden = !active;
                    panel.classList.toggle('hidden', !active);
                });
                labels.forEach((label) => label.classList.toggle('is-active', Number(label.dataset.stepLabel) === step));
                if (step === 2 && map) setTimeout(() => map.invalidateSize(), 0);
                document.querySelector(`[data-wizard-step="${step}"] h2`).focus?.();
            }

            document.getElementById('next-photo').addEventListener('click', () => {
                photoError.textContent = '';
                const file = fileInput.files[0];
                if (!file) { photoError.textContent = 'Pilih foto laporan terlebih dahulu.'; return; }
                if (!photoMetadataReady) { photoError.textContent = 'Tunggu sampai metadata GPS foto selesai diperiksa.'; return; }
                if (!geotagVisibleConfirm.checked) { photoError.textContent = 'Periksa tulisan geotag pada foto, lalu centang konfirmasi.'; return; }
                showStep(2);
            });

            fileInput.addEventListener('change', async () => {
                const generation = ++scanGeneration;
                const file = fileInput.files[0];
                photoMetadataReady = false;
                geotagVisibleConfirm.checked = false;
                latitude.value = '';
                longitude.value = '';
                coordinateLabel.textContent = 'Pilih foto dengan metadata GPS terlebih dahulu.';
                if (marker) { marker.remove(); marker = null; }
                if (radiusCircle) { radiusCircle.remove(); radiusCircle = null; }
                if (nearestMarker) { nearestMarker.remove(); nearestMarker = null; }
                areaLabel.value = '';
                addressInput.value = '';
                photoError.textContent = '';
                geotagStatus.textContent = '';

                if (previewUrl) URL.revokeObjectURL(previewUrl);
                if (!file) return;
                if (file.type !== 'image/jpeg' || file.size > 10 * 1024 * 1024) {
                    photoError.textContent = 'Pilih foto JPG/JPEG dengan ukuran maksimal 10 MB.';
                    fileInput.value = '';
                    photoPreview.classList.add('hidden');
                    photoEmpty.classList.remove('hidden');
                    return;
                }

                previewUrl = URL.createObjectURL(file);
                photoPreview.src = previewUrl;
                photoPreview.classList.remove('hidden');
                photoEmpty.classList.add('hidden');
                geotagStatus.textContent = 'Memeriksa metadata GPS EXIF…';

                const body = new FormData();
                body.append('foto', file);

                try {
                    const response = await fetch('/laporan/lokasi-foto', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body,
                    });
                    const result = await response.json().catch(() => ({}));

                    if (generation !== scanGeneration) return;
                    if (!response.ok) throw new Error(result.errors?.foto?.[0] || 'Metadata GPS tidak berhasil dibaca.');

                    latitude.value = Number(result.latitude).toFixed(7);
                    longitude.value = Number(result.longitude).toFixed(7);
                    const point = L.latLng(Number(result.latitude), Number(result.longitude));
                    map.setView(point, 17);
                    marker = L.marker(point, { draggable: false, alt: 'Lokasi dari metadata GPS foto' }).addTo(map);
                    radiusCircle = L.circle(point, { radius: 50, color: '#E8871E', weight: 2, fillOpacity: 0.08 }).addTo(map);
                    areaLabel.value = [result.kelurahan, result.kecamatan].filter(Boolean).join(', ');
                    addressInput.value = result.alamat || '';
                    const accuracy = result.accuracy === null ? null : Number(result.accuracy);
                    coordinateLabel.textContent = `EXIF ${latitude.value}, ${longitude.value}${Number.isFinite(accuracy) ? ` · akurasi ±${Math.round(accuracy)} m` : ' · akurasi tidak tersedia'}`;
                    if (result.kandidat_titik) {
                        const candidate = result.kandidat_titik;
                        nearestMarker = L.circleMarker([candidate.latitude, candidate.longitude], { radius: 12, color: '#73519a', weight: 3, fillOpacity: 0 }).addTo(map);
                        const candidatePopup = document.createElement('span');
                        candidatePopup.textContent = `Titik #${candidate.id} · ${candidate.jarak_meter} m`;
                        nearestMarker.bindPopup(candidatePopup);
                        geotagStatus.textContent = `Wilayah ${areaLabel.value}. Titik #${candidate.id} terdekat berjarak ${candidate.jarak_meter} m; admin akan mengonfirmasi pengaitan.`;
                    } else {
                        geotagStatus.textContent = `Wilayah ${areaLabel.value}. Tidak ada titik lama dalam radius 50 meter.`;
                    }
                    if (Number.isFinite(accuracy) && accuracy > 50) {
                        geotagStatus.textContent += ' Akurasi GPS lebih rendah dari radius pencocokan; ambil foto ulang di area terbuka bila memungkinkan.';
                    }
                    photoMetadataReady = true;
                } catch (error) {
                    if (generation !== scanGeneration) return;
                    geotagStatus.textContent = '';
                    photoError.textContent = error.message;
                    fileInput.value = '';
                }
            });

            document.getElementById('next-location').addEventListener('click', () => {
                if (!latitude.value || !longitude.value) {
                    coordinateLabel.textContent = 'Koordinat belum tersedia. Pilih foto bergeotag terlebih dahulu.';
                    coordinateLabel.classList.add('text-red-700');
                    return;
                }
                coordinateLabel.classList.remove('text-red-700');
                showStep(3);
            });

            document.querySelectorAll('[data-back]').forEach((button) => button.addEventListener('click', () => showStep(Number(button.dataset.back))));

            map = L.map('report-map', { dragging: false, scrollWheelZoom: false, doubleClickZoom: false, boxZoom: false, keyboard: false }).setView([-3.3194, 114.5908], 13);
            document.getElementById('report-map').__bwMap = map;

            const description = document.getElementById('deskripsi');
            description.addEventListener('input', () => { document.getElementById('description-length').textContent = description.value.length; });
        })();
        </script>
    @endpush
</x-app-layout>
