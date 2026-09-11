<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Dashboard Admin — B-WasteMap
        </h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="p-4 bg-white rounded shadow text-center">
                    <div class="text-2xl font-bold">{{ $totalTitik }}</div>Total Titik
                </div>
                <div class="p-4 bg-white rounded shadow text-center">
                    <div class="text-2xl font-bold">{{ $laporanBaru }}</div>Laporan Baru
                </div>
                <div class="p-4 bg-white rounded shadow text-center">
                    <div class="text-2xl font-bold">{{ $dalamPenanganan }}</div>Dalam Penanganan
                </div>
                <div class="p-4 bg-white rounded shadow text-center">
                    <div class="text-2xl font-bold">{{ $titikBerulang }}</div>Titik Berulang
                </div>
            </div>

            <div class="bg-white rounded shadow p-4 mb-6">
                <canvas id="chartKecamatan"></canvas>
            </div>
            <div class="bg-white rounded shadow p-4">
                <canvas id="chartStatus"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        new Chart(document.getElementById('chartKecamatan'), {
            type: 'bar',
            data: {
                labels: {!! json_encode($perKecamatan->keys()) !!},
                datasets: [{ label: 'Titik per Kecamatan', data: {!! json_encode($perKecamatan->values()) !!}, backgroundColor: '#16a34a' }]
            }
        });
        new Chart(document.getElementById('chartStatus'), {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($perStatus->keys()) !!},
                datasets: [{ data: {!! json_encode($perStatus->values()) !!}, backgroundColor: ['#22c55e','#eab308','#ef4444'] }]
            }
        });
    </script>
</x-app-layout>