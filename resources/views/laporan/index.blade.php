<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Kelola Laporan
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-8">

            @if (session('success'))
                <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- ===== Laporan menunggu verifikasi ===== --}}
            <div>
                <h3 class="font-semibold text-lg mb-3">Menunggu Verifikasi ({{ $laporanBaru->count() }})</h3>

                @forelse($laporanBaru as $l)
                    <div class="bg-white border rounded p-3 mb-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ route('laporan.foto', $l) }}" class="w-20 h-20 object-cover rounded" alt="Foto laporan {{ $l->nomor_laporan }}">
                            <div>
                                <div class="font-medium">{{ $l->jenis_sampah }}</div>
                                <div class="text-sm text-gray-600">{{ $l->deskripsi }}</div>
                                <div class="text-xs text-gray-400">{{ $l->created_at->format('d M Y H:i') }} — oleh {{ $l->pelapor->name ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2 shrink-0">
                            <form method="POST" action="{{ route('admin.laporan.verifikasi', $l) }}">
                                @csrf
                                <button class="bg-green-600 text-white px-3 py-1 rounded text-sm">Verifikasi</button>
                            </form>
                            <form method="POST" action="{{ route('admin.laporan.tolak', $l) }}">
                                @csrf
                                <button class="bg-red-600 text-white px-3 py-1 rounded text-sm">Tolak</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada laporan yang menunggu verifikasi.</p>
                @endforelse
            </div>

            {{-- ===== Laporan terverifikasi, belum ditugaskan ke petugas ===== --}}
            <div>
                <h3 class="font-semibold text-lg mb-3">Menunggu Penugasan Petugas ({{ $laporanDiverifikasi->count() }})</h3>

                @forelse($laporanDiverifikasi as $l)
                    <div class="bg-white border rounded p-3 mb-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ route('laporan.foto', $l) }}" class="w-20 h-20 object-cover rounded" alt="Foto laporan {{ $l->nomor_laporan }}">
                            <div>
                                <div class="font-medium">
                                    {{ $l->jenis_sampah }}
                                    @if($l->titikSampah)
                                        — <a href="{{ route('titik.show', $l->titikSampah) }}" class="text-blue-600 underline text-sm">Titik #{{ $l->titikSampah->id }}</a>
                                    @endif
                                </div>
                                <div class="text-sm text-gray-600">{{ $l->deskripsi }}</div>
                            </div>
                        </div>

                        @if($petugas->count())
                            <form method="POST" action="{{ route('admin.penanganan.tugaskan', $l) }}" class="flex items-center gap-2 shrink-0">
                                @csrf
                                <select name="petugas_id" class="border rounded p-1 text-sm" required>
                                    <option value="">Pilih petugas…</option>
                                    @foreach($petugas as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                                <button class="bg-indigo-600 text-white px-3 py-1 rounded text-sm">Tugaskan</button>
                            </form>
                        @else
                            <span class="text-xs text-gray-400">Belum ada akun petugas</span>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-gray-500">Tidak ada laporan yang menunggu penugasan.</p>
                @endforelse
            </div>

        </div>
    </div>
</x-app-layout>
