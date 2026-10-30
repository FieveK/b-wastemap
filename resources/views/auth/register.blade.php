<x-guest-layout>
    @section('title', 'Daftar · B-WasteMap')

    <header class="auth-heading">
        <p class="auth-kicker">Akun masyarakat</p>
        <h1 class="auth-title">Buat akun B-WasteMap</h1>
        <p class="auth-intro">Daftar untuk melaporkan titik sampah dan mengikuti status penanganannya.</p>
    </header>

    <form class="auth-form" method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label class="auth-label" for="name" value="Nama lengkap" />
            <x-text-input id="name" class="auth-input mt-1" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="auth-error mt-2" />
        </div>

        <div>
            <x-input-label class="auth-label" for="email" value="Alamat email" />
            <x-text-input id="email" class="auth-input mt-1" type="email" name="email" :value="old('email')" required autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="auth-error mt-2" />
        </div>

        <div>
            <x-input-label class="auth-label" for="password" value="Kata sandi" />
            <x-text-input id="password" class="auth-input mt-1" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error mt-2" />
        </div>

        <div>
            <x-input-label class="auth-label" for="password_confirmation" value="Ulangi kata sandi" />
            <x-text-input id="password_confirmation" class="auth-input mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error mt-2" />
        </div>

        <button class="bw-btn bw-btn-orange auth-submit" type="submit">Daftar sebagai warga</button>
        <p class="auth-register">Sudah punya akun? <a class="auth-link" href="{{ route('login') }}">Masuk</a></p>
    </form>
</x-guest-layout>
