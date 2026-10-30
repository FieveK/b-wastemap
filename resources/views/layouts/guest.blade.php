<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', config('app.name', 'B-WasteMap'))</title>
        <link rel="icon" type="image/png" href="{{ asset('images/b-wastemap-logo-badge.png') }}">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <main class="auth-page">
            <div class="auth-card">
                <aside class="auth-brand">
                    <a class="auth-brand-home" href="{{ route('home') }}" aria-label="B-WasteMap, beranda">
                        <img class="auth-brand-logo" src="{{ asset('images/b-wastemap-logo-putih.png') }}" alt="B-WasteMap — Petakan, Pantau, dan Tangani">
                    </a>
                    <div class="auth-brand-copy">
                        <span class="auth-brand-kicker">Bersama jaga Banjarmasin</span>
                        <h2>Lingkungan bersih dimulai dari satu laporan.</h2>
                        <p>Petakan titik sampah liar, pantau prosesnya, dan bantu kota bergerak lebih cepat.</p>
                    </div>
                    <p class="auth-brand-footer"><span aria-hidden="true"></span>Kota Banjarmasin, Kalimantan Selatan</p>
                </aside>
                <section class="auth-content" aria-label="Formulir akun">
                    {{ $slot }}
                </section>
            </div>
        </main>
    </body>
</html>
