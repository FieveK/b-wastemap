<x-app-layout>
    @section('title', 'Lacak laporan · B-WasteMap')
    <main class="bw-container py-10 sm:py-16">
        <section class="bw-card mx-auto max-w-xl p-6 sm:p-9">
            <p class="bw-kicker">Pelacakan publik</p>
            <h1 class="mt-2 text-2xl font-semibold sm:text-3xl">Lacak progres laporan</h1>
            <p class="mt-3 text-sm leading-6 text-[#58665c]">Masukkan token yang muncul setelah laporan dikirim atau pindai QR token. Login tidak diperlukan.</p>
            <form method="POST" action="{{ route('laporan.lacak.cari') }}" class="mt-6">
                @csrf
                <label class="bw-label" for="token">Token laporan</label>
                <input id="token" name="token" class="bw-field mt-2 font-mono uppercase tracking-wider" type="text" value="{{ old('token') }}" required minlength="36" maxlength="36" pattern="BWM-[A-Za-z0-9]{32}" autocomplete="off" placeholder="BWM-…">
                <x-input-error :messages="$errors->get('token')" class="mt-2" />
                <button class="bw-btn bw-btn-primary mt-5 w-full" type="submit">Cari progres</button>
            </form>
            <p class="mt-5 text-xs leading-5 text-[#6c746e]">Token bertindak sebagai kunci akses progres. Jangan bagikan jika tidak ingin orang lain melihat status laporan.</p>
        </section>
    </main>
</x-app-layout>
