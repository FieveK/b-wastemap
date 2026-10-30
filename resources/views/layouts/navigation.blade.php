<nav x-data="{ open: false }" class="site-nav">
    <div class="site-nav-inner">
        <a class="brand" href="{{ route('home') }}" aria-label="B-WasteMap, beranda">
            <img src="{{ asset('images/b-wastemap-logo-utama.png') }}" alt="B-WasteMap — Petakan, Pantau, dan Tangani">
        </a>

        <div class="nav-links">
            <a class="{{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a>
            <a class="{{ request()->routeIs('peta', 'titik.show') ? 'active' : '' }}" href="{{ route('peta') }}">Peta</a>
            <a href="{{ route('laporan.create') }}">Laporkan</a>
            <a class="{{ request()->routeIs('laporan.lacak*') ? 'active' : '' }}" href="{{ route('laporan.lacak') }}">Lacak laporan</a>
            <a href="{{ route('home') }}#tentang">Tentang</a>
            @auth
                @if (Auth::user()->role === 'admin')
                    <a class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}">Pusat admin</a>
                    <a class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">Pengguna</a>
                    <a class="{{ request()->routeIs('admin.analitik*') ? 'active' : '' }}" href="{{ route('admin.analitik') }}">Analitik</a>
                @elseif (Auth::user()->role === 'petugas')
                    <a class="{{ request()->routeIs('petugas.*') ? 'active' : '' }}" href="{{ route('petugas.tugas') }}">Tugas saya</a>
                @else
                    <a class="{{ request()->routeIs('laporan.milik-saya') ? 'active' : '' }}" href="{{ route('laporan.milik-saya') }}">Laporan saya</a>
                @endif
            @endauth
        </div>

        <div class="nav-actions">
            @guest
                <a class="nav-login" href="{{ route('login') }}">Masuk</a>
                <a class="bw-btn bw-btn-orange bw-btn-small" href="{{ route('laporan.create') }}">＋ Buat laporan</a>
            @else
                <a class="nav-login" href="{{ route('profile.edit') }}" aria-label="{{ Auth::user()->role === 'admin' ? 'Pengaturan admin' : 'Profil akun' }}">{{ Auth::user()->role === 'admin' ? 'Pengaturan' : Auth::user()->name }}</a>
                <form method="POST" action="{{ route('logout') }}">@csrf<button class="nav-login" type="submit">Keluar</button></form>
                <a class="bw-btn bw-btn-orange bw-btn-small" href="{{ route('laporan.create') }}">＋ Buat laporan</a>
            @endguest
        </div>

        <button class="nav-toggle" type="button" @click="open = !open" :aria-expanded="open.toString()" aria-controls="mobile-navigation" aria-label="Buka menu">
            <span></span><span></span><span></span>
        </button>
    </div>
    <div id="mobile-navigation" class="mobile-nav" x-cloak x-show="open" @click.outside="open = false">
        <a href="{{ route('home') }}">Beranda</a>
        <a href="{{ route('peta') }}">Peta</a>
        <a href="{{ route('laporan.create') }}">Laporkan titik</a>
        <a href="{{ route('laporan.lacak') }}">Lacak laporan</a>
        <a href="{{ route('home') }}#tentang">Tentang</a>
        @auth
            @if (Auth::user()->role === 'admin')
                <a href="{{ route('admin.dashboard') }}">Pusat admin</a>
                <a href="{{ route('admin.laporan.index') }}">Verifikasi laporan</a>
                <a href="{{ route('admin.users.index') }}">Kelola pengguna</a>
                <a href="{{ route('admin.analitik') }}">Analitik &amp; laporan</a>
            @elseif (Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.tugas') }}">Tugas saya</a>
            @else
                <a href="{{ route('laporan.milik-saya') }}">Laporan saya</a>
            @endif
            <a href="{{ route('profile.edit') }}">{{ Auth::user()->role === 'admin' ? 'Pengaturan' : 'Profil' }}</a>
            <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit">Keluar</button></form>
        @else
            <a href="{{ route('login') }}">Masuk</a>
            <a href="{{ route('register') }}">Daftar</a>
        @endauth
    </div>
</nav>
