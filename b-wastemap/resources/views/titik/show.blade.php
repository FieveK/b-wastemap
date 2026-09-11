<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Titik #{{ $titikSampah->id }} — {{ $titikSampah->alamat ?? 'Tanpa alamat' }}
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white rounded shadow p-4 mb-6 text-sm">
                <p>Kecamatan: <b>{{ $titikSampah->kecamatan ?? '-' }}</b></p>
                <p>Status: <b class="capitalize">{{ str_replace('_', ' ', $titikSampah->status) }}</b></p>
                <p>Total laporan: {{ $titikSampah->total_laporan }} | Total penanganan: {{ $titikSampah->total_penanganan }}</p>
            </div>

            <h2 class="font-semibold mt-4 mb-3">Riwayat (terbaru di atas)</h2>
            <ul class="border-l-2 border-gray-300 pl-4 space-y-4">
                @forelse($riwayat as $r)
                    <li>
                        <b class="capitalize">{{ str_replace('_', ' ', $r->status_kejadian) }}</b>
                        — {{ $r->created_at->format('d M Y H:i') }}<br>
                        <span class="text-sm text-gray-600">{{ $r->keterangan }}</span>

                        @if($r->laporan && $r->laporan->penanganan && $r->laporan->penanganan->status === 'selesai')
                            <div class="mt-1 flex gap-2">
                                @if($r->laporan->penanganan->foto_sebelum)
                                    <a href="{{ asset('storage/'.$r->laporan->penanganan->foto_sebelum) }}" target="_blank" class="text-xs text-blue-600 underline">Foto sebelum</a>
                                @endif
                                @if($r->laporan->penanganan->foto_sesudah)
                                    <a href="{{ asset('storage/'.$r->laporan->penanganan->foto_sesudah) }}" target="_blank" class="text-xs text-blue-600 underline">Foto sesudah</a>
                                @endif
                            </div>
                        @endif
                    </li>
                @empty
                    <li class="text-sm text-gray-500">Belum ada riwayat.</li>
                @endforelse
            </ul>
        </div>
    </div>
</x-app-layout>