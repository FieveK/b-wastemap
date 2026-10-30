<x-app-layout>
    @section('title', 'Kelola pengguna · B-WasteMap')

    <main class="bw-container py-8 sm:py-10">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="bw-kicker">Pusat admin</p>
                <h1 class="mt-1 text-3xl font-semibold">Kelola pengguna</h1>
                <p class="mt-2 text-sm text-[#6c746e]">Atur identitas, peran, dan akses akun B-WasteMap.</p>
            </div>
            <a class="bw-btn bw-btn-primary" href="{{ route('admin.users.index') }}#user-form">＋ Tambah pengguna</a>
        </div>

        @if (session('success'))
            <div class="mt-5 rounded-xl border border-green-200 bg-green-50 p-4 text-sm text-green-800" role="status">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert">
                <p class="font-semibold">Perubahan belum tersimpan.</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('admin.users.index') }}" class="bw-card mt-6 grid gap-3 p-4 sm:grid-cols-2 lg:grid-cols-[minmax(0,1fr)_190px_170px_auto] lg:items-end">
            <div>
                <label class="bw-label" for="user-search">Cari pengguna</label>
                <input id="user-search" class="bw-field" type="search" name="search" value="{{ $search }}" maxlength="100" placeholder="Nama atau email">
            </div>
            <div>
                <label class="bw-label" for="role-filter">Peran</label>
                <select id="role-filter" class="bw-field" name="role">
                    <option value="">Semua peran</option>
                    <option value="masyarakat" @selected(request('role') === 'masyarakat')>Masyarakat</option>
                    <option value="admin" @selected(request('role') === 'admin')>Admin</option>
                    <option value="petugas" @selected(request('role') === 'petugas')>Petugas</option>
                </select>
            </div>
            <div>
                <label class="bw-label" for="status-filter">Status akun</label>
                <select id="status-filter" class="bw-field" name="status">
                    <option value="">Semua status</option>
                    <option value="aktif" @selected(request('status') === 'aktif')>Aktif</option>
                    <option value="nonaktif" @selected(request('status') === 'nonaktif')>Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button class="bw-btn bw-btn-primary" type="submit">Terapkan</button>
                <a class="bw-btn bw-btn-outline" href="{{ route('admin.users.index') }}">Reset</a>
            </div>
        </form>

        <div class="mt-4 grid items-start gap-4 xl:grid-cols-[minmax(0,1.45fr)_minmax(320px,.8fr)]">
            <section class="bw-card overflow-hidden" aria-labelledby="user-list-heading">
                <div class="flex items-center justify-between gap-3 border-b border-[#e8ebe5] px-5 py-4">
                    <div>
                        <h2 id="user-list-heading" class="text-lg font-semibold">Daftar pengguna</h2>
                        <p class="mt-1 text-xs text-[#6c746e]">{{ $users->total() }} akun ditemukan</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[620px] text-left text-sm">
                        <thead class="bg-[#f5f7f4] text-xs uppercase tracking-wide text-[#6c746e]">
                            <tr>
                                <th class="px-5 py-3 font-semibold">Nama / email</th>
                                <th class="px-4 py-3 font-semibold">Peran</th>
                                <th class="px-4 py-3 font-semibold">Status akun</th>
                                <th class="px-5 py-3 text-right font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#e8ebe5]">
                            @forelse ($users as $managedUser)
                                <tr class="{{ $managedUser->is($editingUser) ? 'bg-[#f0f7f3]' : 'bg-white' }}">
                                    <td class="px-5 py-4">
                                        <span class="block font-semibold text-[#17241d]">{{ $managedUser->name }}</span>
                                        <span class="mt-1 block text-xs text-[#6c746e]">{{ $managedUser->email }}</span>
                                    </td>
                                    <td class="px-4 py-4 capitalize">{{ $managedUser->role }}</td>
                                    <td class="px-4 py-4">
                                        <span class="map-status {{ $managedUser->is_active ? 'bg-[#CFE8E1] text-[#1C8C74]' : 'bg-[#eef0ec] text-[#6c746e]' }}">
                                            <span aria-hidden="true">●</span> {{ $managedUser->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        @if ($managedUser->is(auth()->user()))
                                            <span class="text-xs font-medium text-[#6c746e]">Akun Anda</span>
                                        @else
                                            <a class="font-semibold text-[#1C8C74] hover:underline" href="{{ route('admin.users.index', array_merge(request()->query(), ['edit' => $managedUser->id])) }}">Edit pengguna</a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td class="px-5 py-10 text-center text-sm text-[#6c746e]" colspan="4">Tidak ada pengguna yang cocok dengan filter ini.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($users->hasPages())
                    <div class="border-t border-[#e8ebe5] px-5 py-4">{{ $users->links() }}</div>
                @endif
            </section>

            <section id="user-form" class="bw-card p-5 sm:p-6" aria-labelledby="user-form-heading">
                <div>
                    <p class="bw-kicker">{{ $editingUser ? 'Ubah akses' : 'Akun baru' }}</p>
                    <h2 id="user-form-heading" class="mt-1 text-xl font-semibold">{{ $editingUser ? 'Edit pengguna' : 'Tambah pengguna' }}</h2>
                </div>

                <form class="mt-5 flex flex-col gap-4" method="POST" action="{{ $editingUser ? route('admin.users.update', $editingUser) : route('admin.users.store') }}">
                    @csrf
                    @if ($editingUser)
                        @method('PATCH')
                    @endif

                    <div>
                        <label class="bw-label" for="user-name">Nama lengkap</label>
                        <input id="user-name" class="bw-field" type="text" name="name" value="{{ old('name', $editingUser?->name) }}" maxlength="255" autocomplete="name" required>
                    </div>
                    <div>
                        <label class="bw-label" for="user-email">Email</label>
                        <input id="user-email" class="bw-field" type="email" name="email" value="{{ old('email', $editingUser?->email) }}" maxlength="255" autocomplete="email" required>
                    </div>
                    <div>
                        <label class="bw-label" for="user-role">Peran pengguna</label>
                        <select id="user-role" class="bw-field" name="role" required>
                            <option value="masyarakat" @selected(old('role', $editingUser?->role ?? 'masyarakat') === 'masyarakat')>Masyarakat</option>
                            <option value="admin" @selected(old('role', $editingUser?->role) === 'admin')>Admin</option>
                            <option value="petugas" @selected(old('role', $editingUser?->role) === 'petugas')>Petugas</option>
                        </select>
                    </div>
                    <div>
                        <label class="bw-label" for="user-status">Status akun</label>
                        <select id="user-status" class="bw-field" name="is_active" required>
                            <option value="1" @selected((string) old('is_active', $editingUser ? (int) $editingUser->is_active : 1) === '1')>Aktif</option>
                            <option value="0" @selected((string) old('is_active', $editingUser ? (int) $editingUser->is_active : 1) === '0')>Nonaktif</option>
                        </select>
                    </div>
                    <div>
                        <label class="bw-label" for="user-password">{{ $editingUser ? 'Kata sandi baru (opsional)' : 'Kata sandi sementara' }}</label>
                        <input id="user-password" class="bw-field" type="password" name="password" minlength="8" autocomplete="new-password" {{ $editingUser ? '' : 'required' }}>
                        <p class="mt-1 text-xs text-[#6c746e]">{{ $editingUser ? 'Biarkan kosong untuk mempertahankan kata sandi saat ini.' : 'Minimal 8 karakter.' }}</p>
                    </div>
                    @if (! $editingUser)
                        <div>
                            <label class="bw-label" for="user-password-confirmation">Ulangi kata sandi</label>
                            <input id="user-password-confirmation" class="bw-field" type="password" name="password_confirmation" minlength="8" autocomplete="new-password" required>
                        </div>
                    @else
                        <div>
                            <label class="bw-label" for="user-password-confirmation">Konfirmasi kata sandi baru</label>
                            <input id="user-password-confirmation" class="bw-field" type="password" name="password_confirmation" minlength="8" autocomplete="new-password">
                        </div>
                    @endif

                    <div class="rounded-xl bg-[#f5f7f4] p-4 text-sm">
                        <p class="font-semibold text-[#0F3F38]">Akses berdasarkan peran</p>
                        <ul class="mt-2 space-y-1.5 text-xs leading-5 text-[#58655d]">
                            <li><strong>Masyarakat:</strong> mengirim laporan dan melihat riwayat laporannya.</li>
                            <li><strong>Admin:</strong> memverifikasi laporan, menautkan titik, dan mengelola akun.</li>
                            <li><strong>Petugas:</strong> melihat tugas, memperbarui status, serta mengunggah bukti.</li>
                        </ul>
                    </div>

                    <div class="flex flex-wrap gap-2 border-t border-[#e8ebe5] pt-4">
                        @if ($editingUser)
                            <a class="bw-btn bw-btn-outline" href="{{ route('admin.users.index') }}">Batal</a>
                        @endif
                        <button class="bw-btn bw-btn-primary" type="submit">{{ $editingUser ? 'Simpan perubahan' : 'Buat akun' }}</button>
                    </div>
                </form>
            </section>
        </div>

        <p class="mt-4 text-xs text-[#6c746e]">Nonaktifkan akun untuk mencabut akses. Akun tidak dihapus agar laporan dan riwayat tetap tersimpan.</p>
    </main>
</x-app-layout>
