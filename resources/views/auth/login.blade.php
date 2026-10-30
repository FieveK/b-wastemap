<x-guest-layout>
    @section('title', 'Masuk · B-WasteMap')

    <header class="auth-heading">
        <p class="auth-kicker">Masuk ke akun</p>
        <h1 class="auth-title">Selamat datang kembali</h1>
        <p class="auth-intro">Masuk untuk membuat laporan dan memantau penanganan titik sampah.</p>
    </header>

    <x-auth-session-status class="auth-session-status" :status="session('status')" />

    <form class="auth-form" method="POST" action="{{ route('login') }}">
        @csrf

        <div>
            <x-input-label class="auth-label" for="email" value="Alamat email" />
            <x-text-input id="email" class="auth-input mt-1" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="auth-error mt-2" />
        </div>

        <div>
            <x-input-label class="auth-label" for="password" value="Kata sandi" />
            <x-text-input id="password" class="auth-input mt-1" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error mt-2" />
        </div>

        <div class="auth-options">
            <label for="remember_me" class="auth-remember">
                <input id="remember_me" type="checkbox" name="remember" @checked(old('remember'))>
                <span>Ingat saya</span>
            </label>
            @if (Route::has('password.request'))
                <a class="auth-link" href="{{ route('password.request') }}">Lupa kata sandi?</a>
            @endif
        </div>

        <button class="bw-btn bw-btn-orange auth-submit" type="submit">Masuk</button>

        @if (Route::has('register'))
            <p class="auth-register">Belum punya akun? <a class="auth-link" href="{{ route('register') }}">Daftar sebagai warga</a></p>
        @endif
    </form>
</x-guest-layout>
