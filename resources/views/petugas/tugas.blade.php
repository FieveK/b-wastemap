<x-app-layout>
    @section('title', 'Tugas petugas · B-WasteMap')
    <main class="bw-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4"><div><p class="bw-kicker">Portal petugas</p><h1 class="mt-1 text-3xl font-semibold">Tugas penanganan</h1><p class="mt-2 text-sm text-[#6c746e]">Perbarui progres dan simpan bukti setelah pekerjaan selesai.</p></div><a class="bw-btn bw-btn-outline" href="{{ route('peta') }}">Lihat peta</a></div>
        @if (session('success'))<div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('success') }}</div>@endif
        @if ($errors->any())<div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">Periksa kembali status, bukti foto, dan volume sebelum menyimpan.</div>@endif

        <div class="mt-6 grid gap-4 lg:grid-cols-2">
            @forelse ($tugas as $tugasAktif)
                @php($laporan = $tugasAktif->laporan)
                <article class="bw-card overflow-hidden">
                    <div class="grid sm:grid-cols-[190px_minmax(0,1fr)]">
                        <img src="{{ route('laporan.foto', $laporan) }}" class="h-48 w-full object-cover sm:h-full" alt="Foto laporan {{ $laporan->nomor_laporan }}" loading="lazy">
                        <div class="p-5">
                            <div class="flex items-start justify-between gap-3"><div><p class="text-xs font-semibold uppercase tracking-wider text-[#6c746e]">{{ $laporan->nomor_laporan }}</p><h2 class="mt-1 text-lg font-semibold">{{ $laporan->titikSampah?->alamat ?: 'Titik #'.($laporan->titik_sampah_id ?? 'baru') }}</h2></div><span class="map-status {{ $tugasAktif->status === 'dalam_penanganan' ? 'bg-[#CFE8E1] text-[#286d80]' : 'bg-[#fff0dc] text-[#8a4b00]' }}">{{ str_replace('_', ' ', $tugasAktif->status) }}</span></div>
                            <p class="mt-2 text-sm text-[#6c746e]">{{ str_replace('_', ' ', $laporan->jenis_sampah) }} · {{ str_replace('_', ' ', $laporan->perkiraan_kondisi ?: 'Kondisi belum dicatat') }}</p>
                            <p class="mt-2 text-xs text-[#7b847d]">{{ $laporan->alamat ?: 'Alamat belum dicatat' }} · {{ $laporan->kecamatan }}<br>{{ number_format((float) $laporan->latitude, 6) }}, {{ number_format((float) $laporan->longitude, 6) }} · Potensi bahaya: {{ str_replace('_', ' ', $laporan->potensi_bahaya) }}</p>
                            @if ($laporan->deskripsi)<p class="mt-3 text-sm leading-6">{{ $laporan->deskripsi }}</p>@endif
                            <div class="mt-3 flex flex-wrap gap-x-4 gap-y-2 text-sm font-semibold">
                                <a class="text-[#1C8C74]" href="https://www.google.com/maps/dir/?api=1&amp;destination={{ urlencode($laporan->latitude.','.$laporan->longitude) }}" target="_blank" rel="noopener noreferrer">Navigasi GPS ↗</a>
                                @if ($laporan->titikSampah)<a class="text-[#1C8C74]" href="{{ route('titik.show', $laporan->titikSampah) }}">Buka riwayat titik →</a>@endif
                            </div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('petugas.tugas.update', $tugasAktif) }}" enctype="multipart/form-data" class="task-update border-t border-[#e5e8e2] p-5" data-task-form>
                        @csrf
                        <div class="flex flex-wrap items-end gap-3">
                            <div class="min-w-[200px] flex-1"><label class="bw-label" for="task-status-{{ $tugasAktif->id }}">Status penanganan</label><select id="task-status-{{ $tugasAktif->id }}" name="status" class="bw-field" data-task-status required><option value="dalam_penanganan" {{ $tugasAktif->status === 'dalam_penanganan' ? 'selected' : '' }}>Dalam penanganan</option><option value="selesai">Selesai</option></select></div>
                            <p class="text-xs text-[#7b847d]">Foto JPG/PNG maksimal 2 MB dan volume wajib saat tugas selesai.</p>
                        </div>
                        <div class="mt-4 grid gap-3 sm:grid-cols-2">
                            <div><label class="bw-label" for="before-{{ $tugasAktif->id }}">Foto sebelum</label><input id="before-{{ $tugasAktif->id }}" class="bw-field p-2 text-sm" type="file" name="foto_sebelum" accept="image/jpeg,image/png" data-completion-required><x-input-error :messages="$errors->get('foto_sebelum')" class="mt-1" /></div>
                            <div><label class="bw-label" for="after-{{ $tugasAktif->id }}">Foto sesudah</label><input id="after-{{ $tugasAktif->id }}" class="bw-field p-2 text-sm" type="file" name="foto_sesudah" accept="image/jpeg,image/png" data-completion-required><x-input-error :messages="$errors->get('foto_sesudah')" class="mt-1" /></div>
                        </div>
                        <div class="mt-3 grid gap-3 sm:grid-cols-[180px_minmax(0,1fr)]">
                            <div><label class="bw-label" for="volume-{{ $tugasAktif->id }}">Volume diangkut (m³)</label><input id="volume-{{ $tugasAktif->id }}" class="bw-field" type="number" name="volume_m3" min="0" max="100000" step="0.01" data-completion-required></div>
                            <div><label class="bw-label" for="note-{{ $tugasAktif->id }}">Catatan penanganan</label><input id="note-{{ $tugasAktif->id }}" class="bw-field" type="text" name="catatan" maxlength="1000" placeholder="Apa yang dibersihkan atau kendala di lapangan?"></div>
                        </div>
                        <div class="mt-4 flex justify-end"><button class="bw-btn bw-btn-primary" type="submit">Simpan pembaruan</button></div>
                    </form>
                </article>
            @empty
                <div class="bw-card p-8 text-center lg:col-span-2"><span class="mx-auto grid h-12 w-12 place-items-center rounded-full bg-[#CFE8E1] text-xl text-[#1C8C74]">✓</span><h2 class="mt-3 font-semibold">Tidak ada tugas aktif</h2><p class="mt-1 text-sm text-[#6c746e]">Tugas baru dari admin akan tampil di sini.</p></div>
            @endforelse
        </div>
        @if ($tugas->hasPages())
            <div class="mt-5">{{ $tugas->links() }}</div>
        @endif
    </main>
    @push('scripts')
        <script>
        document.querySelectorAll('[data-task-form]').forEach((form) => {
            const status = form.querySelector('[data-task-status]');
            const requiredFields = [...form.querySelectorAll('[data-completion-required]')];
            const update = () => requiredFields.forEach((field) => { field.required = status.value === 'selesai'; });
            status.addEventListener('change', update);
            update();
        });
        </script>
    @endpush
</x-app-layout>
