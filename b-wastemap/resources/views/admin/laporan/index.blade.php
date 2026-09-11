<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Laporan</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto p-4 space-y-8">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
        @endif

        <div>
            <h3 class="font-semibold text-lg mb-3">Menunggu Verifikasi</h3>
            @forelse($laporanBaru as $l)
                <div class="border rounded p-3 mb-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/'.$l->foto) }}" class="w-20 h-20 object-cover rounded">
                        <div>{{ $l->jenis_sampah }} — {{ $l->deskripsi }}</div>
                    </div>
                    <div class="space-x-2">
                        <form method="POST" action="{{ route('admin.laporan.verifikasi', $l) }}" class="inline">
                            @csrf
                            <button class="bg-green-600 text-white px-3 py-1 rounded">Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('admin.laporan.tolak', $l) }}" class="inline">
                            @csrf
                            <button class="bg-red-600 text-white px-3 py-1 rounded">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Tidak ada laporan baru.</p>
            @endforelse
        </div>

        <div>
            <h3 class="font-semibold text-lg mb-3">Sudah Diverifikasi — Belum Ditugaskan</h3>
            @forelse($laporanDiverifikasi as $l)
                <div class="border rounded p-3 mb-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/'.$l->foto) }}" class="w-20 h-20 object-cover rounded">
                        <div>{{ $l->jenis_sampah }} — {{ $l->deskripsi }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.penanganan.tugaskan', $l) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="petugas_id" class="border rounded p-1" required>
                            <option value="">Pilih petugas</option>
                            @foreach($petugas as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded">Tugaskan</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500">Tidak ada laporan yang menunggu penugasan.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>