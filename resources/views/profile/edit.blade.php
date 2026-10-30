<x-app-layout>
    @section('title', 'Pengaturan akun · B-WasteMap')
    <main class="bw-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="bw-kicker">Profil &amp; keamanan</p>
                <h1 class="mt-1 text-3xl font-semibold">Pengaturan akun</h1>
                <p class="mt-2 text-sm text-[#6c746e]">Perbarui identitas dan kata sandi akun B-WasteMap.</p>
            </div>
            @if ($user->role === 'admin')
                <a class="bw-btn bw-btn-outline" href="{{ route('admin.dashboard') }}">Kembali ke pusat admin</a>
            @endif
        </div>

        <div class="mt-6 grid gap-4 {{ $user->role === 'admin' ? 'lg:grid-cols-[1.2fr_.8fr]' : '' }}">
            <div class="space-y-4">
                <section class="bw-card p-5 sm:p-7" aria-labelledby="profile-information-heading">
                    <h2 id="profile-information-heading" class="mb-5 text-lg font-semibold">Profil akun</h2>
                    @include('profile.partials.update-profile-information-form')
                </section>

                <section class="bw-card p-5 sm:p-7" aria-labelledby="profile-password-heading">
                    <h2 id="profile-password-heading" class="mb-5 text-lg font-semibold">Keamanan akun</h2>
                    @include('profile.partials.update-password-form')
                </section>
            </div>

            @if ($user->role === 'admin')
                <aside class="space-y-4" aria-label="Referensi aturan sistem">
                    <section class="bw-card p-5 sm:p-6">
                        <p class="bw-kicker">Referensi aturan</p>
                        <h2 class="mt-1 text-lg font-semibold">Aturan unggahan</h2>
                        <p class="mt-3 text-2xl font-semibold text-[#0F3F38]">JPG / JPEG / PNG</p>
                        <p class="mt-1 text-sm text-[#6c746e]">Ukuran maksimal 2 MB per foto.</p>
                        <p class="mt-4 rounded-lg bg-[#fff0dc] p-3 text-sm leading-6 text-[#684a2a]">Foto laporan dan bukti sebelum-sesudah mengikuti batas yang sama.</p>
                    </section>

                    <section class="bw-card p-5 sm:p-6">
                        <p class="bw-kicker">Pemeriksaan lokasi</p>
                        <h2 class="mt-1 text-lg font-semibold">Aturan kedekatan titik</h2>
                        <p class="mt-3 text-3xl font-semibold text-[#0F3F38]">±50 meter</p>
                        <p class="mt-2 text-sm leading-6 text-[#6c746e]">Admin memilih apakah laporan ditautkan ke titik lama atau dicatat sebagai titik baru. Kejadian dan alasan keputusan tersimpan pada riwayat.</p>
                        <p class="mt-4 rounded-lg bg-[#f0e7f7] p-3 text-sm leading-6 text-[#635777]">Pemeriksaan memakai jarak koordinat. Sistem tidak menilai kemiripan gambar.</p>
                    </section>
                </aside>
            @endif
        </div>

        <section class="bw-card mt-4 p-5 sm:p-7" aria-labelledby="profile-delete-heading">
            <h2 id="profile-delete-heading" class="mb-4 text-lg font-semibold">Hapus akun</h2>
            @include('profile.partials.delete-user-form')
        </section>
    </main>
</x-app-layout>
