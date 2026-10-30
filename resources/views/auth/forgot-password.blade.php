<x-guest-layout>
    @section('title', 'Atur ulang kata sandi · B-WasteMap')

    <header class="auth-heading">
        <p class="auth-kicker">Bantuan akun</p>
        <h1 class="auth-title">Lupa kata sandi?</h1>
        <p class="auth-intro">Masukkan alamat email akun. Kami akan mengirim tautan untuk membuat kata sandi baru.</p>
    </header>

    <x-auth-session-status class="auth-session-status" :status="session('status')" />

    <form class="auth-form" method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <x-input-label class="auth-label" for="email" value="Alamat email" />
            <x-text-input id="email" class="auth-input mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="auth-error mt-2" />
        </div>
        <button class="bw-btn bw-btn-orange auth-submit" type="submit">Kirim tautan reset</button>
        <p class="auth-register"><a class="auth-link" href="{{ route('login') }}">Kembali ke halaman masuk</a></p>
    </form>
</x-guest-layout>
