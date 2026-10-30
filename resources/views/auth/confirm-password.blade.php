<x-guest-layout>
    @section('title', 'Konfirmasi kata sandi · B-WasteMap')

    <header class="auth-heading">
        <p class="auth-kicker">Keamanan akun</p>
        <h1 class="auth-title">Konfirmasi identitas</h1>
        <p class="auth-intro">Masukkan kata sandi Anda untuk melanjutkan ke bagian akun yang aman.</p>
    </header>

    <form class="auth-form" method="POST" action="{{ route('password.confirm') }}">
        @csrf
        <div>
            <x-input-label class="auth-label" for="password" value="Kata sandi" />
            <x-text-input id="password" class="auth-input mt-1" type="password" name="password" required autofocus autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error mt-2" />
        </div>
        <button class="bw-btn bw-btn-orange auth-submit" type="submit">Konfirmasi</button>
    </form>
</x-guest-layout>
