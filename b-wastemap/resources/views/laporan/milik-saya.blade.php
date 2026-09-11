<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Laporan Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <a href="{{ route('laporan.create') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded text-sm mb-2">
                + Buat Laporan Baru
            </a>

            @php
                $badge = [
                    'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800',
                    'diverifikasi' => 'bg-blue-100 text-blue-800',
                    'ditolak' => 'bg-red-100 text-red-800',
                ];
                $label = [
                    'menunggu_verifikasi' => 'Menunggu Verifikasi',
                    'diverifikasi' => 'Diverifikasi',
                    'ditolak' => 'Ditolak',
                ];
                $labelPenanganan = [
                    'ditugaskan' => 'Petugas ditugaskan',
                    'dalam_penanganan' => 'Sedang ditangani',
                    'selesai' => 'Selesai ditangani',
                ];
            @endphp

            @forelse($laporan as $l)
                <div class="bg-white border rounded p-3 flex gap-3">
                    <img src="{{ asset('storage/'.$l->foto) }}" class="w-20 h-20 object-cover rounded shrink-0">
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <div class="font-medium">{{ $l->jenis_sampah }}</div>
                                <div class="text-sm text-gray-600">{{ $l->deskripsi }}</div>
                            </div>
                            <span class="text-xs px-2 py-1 rounded {{ $badge[$l->status] ?? 'bg-gray-100 text-gray-700' }}">
                                {{ $label[$l->status] ?? $l->status }}
                            </span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">Dikirim {{ $l->created_at->format('d M Y H:i') }}</div>

                        @if($l->titikSampah)
                            <a href="{{ route('titik.show', $l->titikSampah) }}" class="text-xs text-blue-600 underline">
                                Lihat riwayat titik #{{ $l->titikSampah->id }} →
                            </a>
                        @endif

                        @if($l->penanganan)
                            <div class="text-xs text-gray-500 mt-1">
                                Status penanganan: {{ $labelPenanganan[$l->penanganan->status] ?? $l->penanganan->status }}
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-500">Anda belum pernah mengirim laporan.</p>
            @endforelse

        </div>
    </div>
</x-app-layout>