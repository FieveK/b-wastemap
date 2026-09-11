# B-WasteMap

**B-WasteMap** adalah aplikasi web pemetaan dan pelaporan titik sampah liar berbasis **Laravel**, dibuat untuk membantu warga dan pemerintah **Kota Banjarmasin** memantau, melaporkan, dan menangani tumpukan sampah liar secara kolaboratif dan berbasis data lokasi.

Warga dapat melaporkan temuan sampah liar lengkap dengan foto dan titik koordinat langsung dari peta. Sistem secara otomatis mengelompokkan laporan yang berdekatan ke satu titik yang sama, menandai titik yang berulang kali dilaporkan, lalu admin dapat memverifikasi dan menugaskan petugas kebersihan untuk menanganinya.

---

## Daftar Isi

- [Fitur Utama](#fitur-utama)
- [Alur Kerja Aplikasi](#alur-kerja-aplikasi)
- [Logika Inti: Deteksi Titik Berulang](#logika-inti-deteksi-titik-berulang)
- [Peran Pengguna](#peran-pengguna)
- [Teknologi yang Digunakan](#teknologi-yang-digunakan)
- [Skema Basis Data](#skema-basis-data)
- [Instalasi](#instalasi)
- [Akun Contoh (Seeder)](#akun-contoh-seeder)
- [Menjalankan Pengujian](#menjalankan-pengujian)
- [Struktur Folder Penting](#struktur-folder-penting)

---

## Fitur Utama

- 🗺️ **Peta interaktif publik** — menampilkan seluruh titik sampah di Kota Banjarmasin menggunakan Leaflet.js + OpenStreetMap, tanpa perlu login.
- 🎨 **Indikator status berwarna** — hijau (normal), kuning (perlu perhatian), merah (titik berulang), langsung terlihat di peta.
- 📸 **Form pelaporan berbasis peta** — warga cukup klik lokasi di peta dan mengunggah foto untuk membuat laporan baru.
- 🤖 **Pengelompokan lokasi otomatis** — laporan baru dalam radius 50 meter dari titik yang sudah ada akan otomatis digabungkan ke titik tersebut (lihat [Logika Inti](#logika-inti-deteksi-titik-berulang)).
- ✅ **Verifikasi & penolakan laporan oleh admin** sebelum laporan masuk ke sistem penanganan.
- 👷 **Penugasan & pelacakan penanganan petugas** — lengkap dengan foto sebelum/sesudah dan catatan pekerjaan.
- 📜 **Riwayat per titik lokasi** — jejak audit setiap kejadian: dilaporkan → diverifikasi → dibersihkan → muncul kembali.
- 📊 **Dashboard admin** — total titik, laporan baru, laporan dalam penanganan, jumlah titik berulang, serta grafik distribusi per kecamatan dan per status (Chart.js).
- 🔐 **Kontrol akses berbasis peran** (admin, petugas, masyarakat) melalui middleware khusus.
- 🔑 **Autentikasi lengkap** (login, registrasi, verifikasi email, reset password) menggunakan Laravel Breeze.

## Alur Kerja Aplikasi

```
1. Warga (masyarakat) membuka peta publik → klik "Laporkan" → pilih lokasi di peta,
   unggah foto, isi jenis & deskripsi sampah → kirim laporan.
        ↓
2. Laporan berstatus "menunggu_verifikasi".
        ↓
3. Admin meninjau laporan masuk:
      - Diverifikasi → sistem otomatis mencari/mencocokkan titik_sampah terdekat.
      - Ditolak      → laporan berhenti di sini.
        ↓
4. Admin menugaskan seorang Petugas ke laporan yang sudah terverifikasi.
        ↓
5. Petugas memperbarui status penanganan: ditugaskan → dalam_penanganan → selesai
   (dengan foto sebelum & sesudah + catatan).
        ↓
6. Saat selesai, riwayat titik dicatat dan status titik_sampah diperbarui otomatis
   (normal / perlu_perhatian / berulang) berdasarkan jumlah laporan yang diterima.
```

## Logika Inti: Deteksi Titik Berulang

Bagian paling khas dari aplikasi ini ada di `App\Services\TitikSampahService`. Ketika admin memverifikasi sebuah laporan:

1. Sistem menghitung jarak laporan tersebut ke semua titik sampah yang sudah tercatat menggunakan **formula Haversine** langsung di level query database.
2. Jika ditemukan titik dalam radius **50 meter**, laporan digabungkan ke titik itu (`total_laporan` bertambah, tidak membuat duplikat titik baru).
3. Jika tidak ada titik dalam radius tersebut, sebuah `TitikSampah` baru dibuat.
4. Status titik diperbarui otomatis berdasarkan jumlah laporan yang masuk:

   | Jumlah `total_laporan` | Status            | Warna di peta |
   |---|---|---|
   | 1                     | `normal`           | 🟢 Hijau  |
   | 2–3                   | `perlu_perhatian`  | 🟡 Kuning |
   | > 3                   | `berulang`         | 🔴 Merah  |

5. Setiap perubahan dicatat sebagai entri baru pada `RiwayatTitik` untuk keperluan audit/histori.

## Peran Pengguna

| Peran | Hak Akses |
|---|---|
| **Masyarakat** (default) | Melihat peta publik, membuat laporan, melihat daftar laporan miliknya sendiri |
| **Admin** | Semua hak masyarakat + dashboard statistik, verifikasi/tolak laporan, menugaskan petugas |
| **Petugas** | Melihat daftar tugas penanganan yang diberikan kepadanya, memperbarui status & bukti penanganan |

Kontrol akses diterapkan melalui middleware `role:admin` / `role:petugas` (lihat `app/Http/Middleware/RoleMiddleware.php`).

## Teknologi yang Digunakan

**Backend**
- PHP ^8.3
- Laravel Framework ^13.17
- Laravel Breeze (scaffolding autentikasi)
- SQLite (default, dapat diganti MySQL/PostgreSQL di `.env`)

**Frontend**
- Blade Templates
- Tailwind CSS 3 + Alpine.js
- Vite (build tool)
- Leaflet.js 1.9.4 + OpenStreetMap (peta interaktif)
- Chart.js (grafik statistik dashboard)

**Pengujian**
- PHPUnit ^12

## Skema Basis Data

Tabel utama di luar skema bawaan Laravel (users, sessions, dll.):

| Tabel | Deskripsi | Relasi Penting |
|---|---|---|
| `titik_sampah` | Satu titik lokasi unik di peta beserta status & akumulasi jumlah laporan/penanganan | `hasMany` Laporan, `hasMany` RiwayatTitik |
| `laporan` | Laporan yang dikirim warga (foto, koordinat, jenis sampah, status verifikasi) | `belongsTo` User (pelapor), `belongsTo` TitikSampah, `hasOne` Penanganan |
| `penanganan` | Proses penindaklanjutan oleh petugas atas sebuah laporan | `belongsTo` Laporan, `belongsTo` User (petugas) |
| `riwayat_titik` | Log/histori kejadian pada sebuah titik sampah | `belongsTo` TitikSampah, `belongsTo` Laporan |

Kolom `role` pada tabel `users` bertipe enum: `admin`, `petugas`, `masyarakat` (default).

## Instalasi

**Prasyarat:** PHP ^8.3, Composer, Node.js & npm.

```bash
# 1. Masuk ke folder project
cd b-wastemap

# 2. Install dependency PHP
composer install

# 3. Salin file environment & generate key aplikasi
cp .env.example .env
php artisan key:generate

# 4. Jalankan migrasi database (default: SQLite)
touch database/database.sqlite   # jika DB_CONNECTION=sqlite
php artisan migrate

# (opsional) isi akun contoh
php artisan db:seed

# 5. Buat symlink storage agar foto laporan bisa diakses publik
php artisan storage:link

# 6. Install dependency frontend & build asset
npm install
npm run build
```

Jalankan mode pengembangan (server + queue + Vite sekaligus, disediakan oleh script `dev` di `composer.json`):

```bash
composer run dev
```

Atau secara manual:

```bash
php artisan serve   # http://localhost:8000
npm run dev          # Vite dev server, di terminal terpisah
```

## Akun Contoh (Seeder)

Setelah menjalankan `php artisan db:seed`, tiga akun berikut tersedia (password untuk semuanya: `password`):

| Peran | Email | Password |
|---|---|---|
| Admin | `admin@bwastemap.test` | `password` |
| Petugas | `petugas@bwastemap.test` | `password` |
| Masyarakat | `warga@bwastemap.test` | `password` |

## Menjalankan Pengujian

```bash
composer test
# atau
php artisan test
```

Pengujian mencakup alur autentikasi bawaan Breeze, manajemen profil, serta pengujian khusus logika penggabungan titik berdasarkan radius (`tests/Feature/TitikSampahServiceTest.php`).

## Struktur Folder Penting

```
app/
├── Http/Controllers/
│   ├── PetaController.php              # Peta publik + API data titik
│   ├── LaporanController.php           # Pembuatan laporan oleh warga
│   ├── TitikSampahController.php       # Detail & riwayat satu titik
│   ├── AdminDashboardController.php    # Statistik dashboard admin
│   ├── AdminLaporanController.php      # Verifikasi/tolak laporan
│   ├── AdminPenangananController.php   # Penugasan petugas
│   └── PetugasController.php           # Daftar & update tugas petugas
├── Http/Middleware/RoleMiddleware.php  # Guard akses berbasis peran
├── Models/                             # TitikSampah, Laporan, Penanganan, RiwayatTitik, User
└── Services/TitikSampahService.php     # Logika Haversine & klasifikasi status titik

resources/views/
├── peta/index.blade.php                # Peta publik (Leaflet)
├── laporan/                            # Form & daftar laporan warga
├── admin/                              # Dashboard & manajemen laporan admin
└── petugas/tugas.blade.php             # Daftar tugas petugas

database/migrations/                    # Skema titik_sampah, laporan, penanganan, riwayat_titik
routes/web.php                          # Definisi seluruh rute aplikasi
```

---

Dibangun di atas [Laravel](https://laravel.com) dengan [Laravel Breeze](https://laravel.com/docs/starter-kits#breeze).
