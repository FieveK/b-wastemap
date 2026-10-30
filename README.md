# B-WasteMap

Sistem web pemetaan dan pemantauan riwayat titik sampah liar di Kota Banjarmasin. Warga mengirim laporan berfoto, admin memverifikasi dan menugaskan petugas, lalu petugas mencatat hasil penanganan. Laporan dalam radius 50 meter dapat ditautkan ke riwayat titik yang sama.

## Fitur

- Peta publik dengan pencarian, filter status, dan riwayat titik.
- Warga dapat melapor tanpa login. Foto JPG/JPEG bergeotag dipakai untuk membaca koordinat GPS EXIF; wilayah kelurahan/kecamatan diambil dari batas GeoJSON, alamat dicari dari koordinat, dan akurasi GPS ditampilkan.
- Foto Open Camera maksimal 10 MB; tulisan lokasi harus terlihat pada foto dan koordinatnya cocok dengan titik peta.
- Laporan aktif pada lokasi dan hari yang sama dalam radius 50 meter digabungkan dengan bukti terbaru. Setelah penanganan selesai, laporan baru membuat kejadian baru.
- Verifikasi admin, pemeriksaan kandidat titik dalam radius 50 meter, dan penugasan petugas.
- Pembaruan status petugas dengan foto sebelum-sesudah dan catatan.
- Token progres anonim, pelacakan status publik, dan QR yang dapat diunduh.
- Notifikasi pop-up website untuk admin; akun admin/petugas punya alur lupa password.
- Dashboard, analitik dengan filter tanggal/wilayah, ekspor CSV, dan pengelolaan akun.

## Menjalankan di Laragon

Prasyarat: PHP 8.3+ dengan ekstensi `exif` aktif, Composer, Node.js/npm, dan MySQL.

Di Open Camera, aktifkan penyimpanan data lokasi/GPS dan stempel lokasi pada foto. Form membaca koordinat dari EXIF; unggah foto JPG/JPEG asli maksimal 10 MB. Pastikan tulisan lokasi juga terlihat pada gambar.

1. Buka terminal di folder proyek, lalu jalankan:

   ```powershell
   composer install
   npm install
   ```

2. Buat database MySQL bernama `b_wastemap` melalui phpMyAdmin atau MySQL.
3. Salin `.env.example` menjadi `.env` jika file `.env` belum ada. Pastikan `DB_DATABASE=b_wastemap`, `DB_USERNAME=root`, dan `DB_PASSWORD` sesuai Laragon.
4. Siapkan aplikasi dan data awal:

   ```powershell
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   npm run build
   ```

5. Jalankan aplikasi:

   ```powershell
   php artisan serve
   ```

   Buka `http://127.0.0.1:8000`.

Reset password memakai email Laravel. `.env.example` memakai `MAIL_MAILER=log`; atur SMTP di `.env` agar tautan reset terkirim ke kotak masuk.

## Akun demo lokal

| Peran | Email | Kata sandi |
| --- | --- | --- |
| Admin | `admin@bwastemap.test` | `password` |
| Petugas | `petugas@bwastemap.test` | `password` |
| Masyarakat | `warga@bwastemap.test` | `password` |

Akun demo dibuat oleh seeder untuk pengembangan lokal. Ganti atau hapus sebelum aplikasi dipasang di server publik.
