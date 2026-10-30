<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <meta name="theme-color" content="#0A332D">
        <link rel="icon" type="image/png" href="{{ asset('images/b-wastemap-logo-badge.png') }}">
        <title>@yield('title', 'B-WasteMap · Kota Banjarmasin')</title>

        @stack('styles')

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-[#F5F8F7]">
            @include('layouts.navigation')
            @auth
                @if (auth()->user()->role === 'admin')
                    <div id="admin-report-notifications" data-url="{{ route('admin.notifikasi.laporan') }}" class="fixed right-4 top-20 z-[2000] flex flex-col gap-3" aria-live="polite"></div>
                @endif
            @endauth

            <!-- Page Heading -->
            @isset($header)
                <header class="page-heading">
                    <div class="max-w-7xl mx-auto py-5 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <div class="page-content">
                {{ $slot }}
            </div>
        </div>
        @stack('scripts')
    </body>
</html>
