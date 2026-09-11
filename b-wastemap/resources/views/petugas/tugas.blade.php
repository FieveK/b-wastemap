<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Tugas Penanganan Saya
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="bg-green-100 text-green-800 border border-green-300 rounded p-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @forelse($tugas as $t)
                <div class="bg-white border rounded p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <div class="font-medium">{{ $t->laporan->jenis_sampah }}</div>
                            <div class="text-sm text-gray-600">{{ $t->laporan->deskripsi }}</div>
                            @if($t->laporan->titikSampah)
                                <a href="{{ route('titik.show', $t->laporan->titikSampah) }}" class="text-xs text-blue-600 underline">
                                    Titik #{{ $t->laporan->titikSampah->id }} — lihat di peta / riwayat →
                                </a>
                            @endif
                        </div>
                        <span class="text-xs px-2 py-1 rounded bg-indigo-100 text-indigo-800 capitalize">
                            {{ str_replace('_', ' ', $t->status) }}
                        </span>
                    </div>

                    <img src="{{ asset('storage/'.$t->laporan->foto) }}" class="w-24 h-24 object-cover rounded mb-3">

                    <form method="POST" action="{{ route('petugas.tugas.update', $t) }}" enctype="multipart/form-data" class="space-y-3 border-t pt-3">
                        @csrf
                        <div>
                            <x-input-label value="Status Penanganan" />
                            <select name="status" class="border rounded p-2 w-full mt-1" required>
                                <option value="dalam_penanganan" {{ $t->status === 'dalam_penanganan' ? 'selected' : '' }}>Dalam Penanganan</option>
                                <option value="selesai">Selesai</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <x-input-label value="Foto Sebelum" />
                                <input type="file" name="foto_sebelum" accept="image/*" class="text-sm mt-1">
                            </div>
                            <div>
                                <x-input-label value="Foto Sesudah" />
                                <input type="file" name="foto_sesudah" accept="image/*" class="text-sm mt-1">
                            </div>
                        </div>
                        <div>
                            <x-input-label value="Catatan" />
                            <textarea name="catatan" class="border rounded p-2 w-full mt-1" rows="2"></textarea>
                        </div>
                        <x-primary-button>Perbarui Status</x-primary-button>
                    </form>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada tugas yang ditugaskan ke Anda.</p>
            @endforelse

        </div>
    </div>
</x-app-layout>