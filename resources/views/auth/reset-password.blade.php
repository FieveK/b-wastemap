<x-guest-layout>
    @section('title', 'Kata sandi baru · B-WasteMap')

    <header class="auth-heading">
        <p class="auth-kicker">Bantuan akun</p>
        <h1 class="auth-title">Buat kata sandi baru</h1>
        <p class="auth-intro">Gunakan kata sandi kuat yang belum digunakan pada akun lain.</p>
    </header>

    <form class="auth-form" method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label class="auth-label" for="email" value="Alamat email" />
            <x-text-input id="email" class="auth-input mt-1" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="email" />
            <x-input-error :messages="$errors->get('email')" class="auth-error mt-2" />
        </div>

        <div>
            <x-input-label class="auth-label" for="password" value="Kata sandi baru" />
            <x-text-input id="password" class="auth-input mt-1" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="auth-error mt-2" />
        </div>

        <div>
            <x-input-label class="auth-label" for="password_confirmation" value="Ulangi kata sandi baru" />
            <x-text-input id="password_confirmation" class="auth-input mt-1" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="auth-error mt-2" />
        </div>

        <button class="bw-btn bw-btn-orange auth-submit" type="submit">Simpan kata sandi baru</button>
    </form>
</x-guest-layout>
