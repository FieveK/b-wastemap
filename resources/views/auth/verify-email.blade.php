<x-guest-layout>
    @section('title', 'Verifikasi email · B-WasteMap')

    <header class="auth-heading">
        <p class="auth-kicker">Verifikasi akun</p>
        <h1 class="auth-title">Periksa email Anda</h1>
        <p class="auth-intro">Klik tautan verifikasi yang kami kirim untuk mengaktifkan akun.</p>
    </header>

    <x-auth-session-status class="auth-session-status" :status="session('status')" />
    <x-input-error :messages="$errors->get('email')" class="auth-error mb-4" />

    <div class="auth-form">
        <p class="text-sm leading-6 text-[#60736E]">Belum menerima email? Kirim ulang tautan verifikasi atau keluar untuk menggunakan akun lain.</p>
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button class="bw-btn bw-btn-orange auth-submit" type="submit">Kirim ulang tautan</button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="auth-link" type="submit">Keluar</button>
        </form>
    </div>
</x-guest-layout>
