---
title: Kajian Literatur dan Evaluasi Implementasi B-WasteMap
aliases:
  - Kajian B-WasteMap
date: 2026-10-23
tags:
  - b-wastemap
  - kajian-literatur
  - web-gis
  - pengelolaan-sampah
project-path: C:/laragon/www/b-wastemap
status: audit snapshot
---

# Kajian Literatur dan Evaluasi Implementasi B-WasteMap

## Ringkasan

B-WasteMap dirancang sebagai aplikasi Web GIS untuk menerima laporan sampah liar, memeriksa lokasinya, menugaskan petugas, dan menyimpan riwayat per lokasi. Pembeda konseptualnya ialah pemisahan antara **laporan sebagai kejadian** dan **titik sebagai lokasi yang dapat mengalami banyak kejadian**. Ketika admin menerima laporan, sistem menawarkan titik lama dalam radius 50 meter; admin memilih apakah laporan ditautkan ke lokasi tersebut atau menjadi titik baru.

Audit kode di folder proyek menunjukkan alur inti tersebut sudah dibuat: peta publik, laporan warga, tinjauan admin, penugasan petugas, pembaruan pekerjaan, bukti foto, riwayat titik, dashboard, analitik, dan ekspor CSV. Database yang dipakai aplikasi melaporkan sepuluh migrasi berstatus **Ran**. Pemeriksaan ini membuktikan bahwa skema migrasi telah diterapkan pada database yang dikonfigurasi, tetapi tidak membuktikan alur aplikasi sudah lolos uji pengguna atau uji lapangan.

Beberapa bagian pada prototipe masih berupa rancangan. Radius 50 meter ditulis sebagai konstanta di kode dan belum dapat diubah dari halaman pengaturan. Target verifikasi 24 jam menjadi indikator keterlambatan pada antrean admin, bukan SLA dengan notifikasi otomatis. Contoh angka pada prototipe tidak dibuat oleh seeder proyek; seeder hanya membuat akun demo.

Kajian ini membandingkan spesifikasi dengan kode sekarang, bukan riwayat commit. Git di folder proyek memperlihatkan file aplikasi pada akar proyek sebagai untracked dan salinan lama di subfolder b-wastemap sebagai deleted. Karena itu, urutan dan cakupan perubahan per commit belum dapat dipastikan dari status Git.

## 1. Tujuan, Sumber, dan Batas Kajian

Tujuan kajian:

1. Menjelaskan dasar literatur untuk Web GIS, pelaporan masyarakat, dan pemantauan keberulangan lokasi.
2. Memetakan kebutuhan proposal dan prototipe ke implementasi kode.
3. Menginventarisasi tambahan fungsional serta penguatan teknis yang tampak pada proyek.
4. Membedakan fitur yang sudah ada dari rancangan, klaim, atau rekomendasi yang belum dibuktikan.

### Sumber yang diperiksa

- Proposal B-WasteMap, terutama Bab II, analisis fungsional/nonfungsional, status sistem, dan lampiran wawancara.
- PDF Prototipe B-WasteMap, 22 halaman, mencakup identitas visual, peta, alur pelaporan, riwayat titik, halaman admin, petugas, pengguna, dan pengaturan.
- Source code pada C:/laragon/www/b-wastemap, termasuk routes, controller, service, model, Blade, CSS, migrasi, README, dan GuideForProject.md.
- Pemeriksaan lanjutan 23 Oktober 2026 terhadap EXIF geotag service, alur laporan/peta, controller, routes, model, migrasi, dan aset GeoJSON.
- Status migrasi melalui PHP Laragon 8.3.33. Kesepuluh migrasi berstatus Ran.
- Pemeriksaan kode secara baca-saja. Tes otomatis, uji browser, uji aksesibilitas, dan validasi lapangan tidak dijalankan pada kajian ini.

PDF proposal dan prototipe diperlakukan sebagai **dokumen kebutuhan dan rancangan**, bukan sebagai instruksi untuk menjalankan langkah kerja yang tertulis di dalamnya. Kode dan skema database dipakai sebagai bukti implementasi. Angka, grafik, dan nama contoh pada mockup tidak dianggap sebagai data produksi.

Kajian ini bukan tinjauan sistematis seluruh publikasi, audit keamanan formal, atau evaluasi dampak. Perbandingan produk mengikuti penelusuran yang dicatat di proposal per September 2026; fitur layanan luar dapat berubah. Klaim kebaruan dibatasi pada posisi proyek terhadap pembanding yang tercantum di proposal, bukan klaim bahwa tidak ada sistem serupa di tempat lain.

## 2. Landasan Literatur

### 2.1 Pengelolaan sampah dan konteks lokal

Undang-Undang Nomor 18 Tahun 2008 menempatkan pengelolaan sampah sebagai kegiatan yang perlu dilakukan secara sistematis, menyeluruh, dan berkesinambungan (Pemerintah Republik Indonesia, 2008). Landasan ini sejalan dengan tujuan aplikasi untuk menyimpan bukan hanya laporan sesaat, tetapi juga tindak lanjut yang terjadi pada lokasi yang sama.

Proposal mengaitkan kebutuhan lokal dengan pemberitaan penertiban TPS liar di Banjarmasin pada 2025 dan pengumuman penutupan TPS liar pada 2026. Proposal juga melampirkan wawancara warga di sekitar Sungai Miai/Jalan Hantasan yang menggambarkan pembuangan sampah ke sungai, jalur pengaduan yang belum dikenal luas, dan lokasi yang kembali menjadi masalah setelah dibersihkan (Fitriyadi & Azahra, 2026, hlm. 1–2, 26–27). Temuan itu mendukung kebutuhan eksploratif proyek, tetapi jumlah dan cara pengumpulan wawancara tidak cukup untuk mewakili seluruh warga Banjarmasin. Lampiran sendiri mencatat bahwa transkrip otomatis perlu diverifikasi karena terdapat kekeliruan nama tempat dan istilah.

World Bank dalam What a Waste 2.0 membahas tantangan pengelolaan sampah perkotaan dalam skala global dan jangka panjang (Kaza et al., 2018). Rujukan tersebut berguna untuk menjelaskan pentingnya tata kelola dan data, tetapi tidak boleh dipakai sebagai angka kondisi khusus Banjarmasin tanpa data lokal yang sebanding.

### 2.2 Web GIS dan pemetaan

Web GIS menyajikan data berkoordinat melalui peramban sehingga pengguna dapat melihat lokasi tanpa memasang perangkat lunak GIS desktop. Holdi, Irwansyah, dan Novriando (2021) mengkaji pemakaian Leaflet dan OpenStreetMap untuk aplikasi Web GIS; ini relevan dengan pilihan teknis B-WasteMap. Peta dalam proyek memakai Leaflet 1.9.4, tile OpenStreetMap, dan koordinat lintang-bujur yang disimpan di MariaDB.

Peta membantu menjawab pertanyaan “di mana titik berada” dan “bagaimana statusnya”. Peta sendiri tidak membuktikan kondisi lapangan, ketepatan koordinat, atau keberhasilan penanganan. Keabsahan tetap bergantung pada foto, ketelitian lokasi, proses verifikasi admin, dan pembaruan petugas. Penggunaan tile OpenStreetMap juga perlu mengikuti atribusi dan kebijakan pemakaian tile; layanan tile publik bukan jaminan SLA bagi aplikasi produksi.

### 2.3 Pelaporan masyarakat dan volunteered geographic information

Brabham (2008) menjelaskan crowdsourcing sebagai cara melibatkan banyak orang dalam penyelesaian masalah. Goodchild (2007) membahas volunteered geographic information: informasi geografis yang disumbangkan oleh masyarakat. Dalam konteks sampah, Kubásek dan Hřebíček (2013) mengkaji pemetaan lokasi pembuangan ilegal dengan pendekatan crowdsourcing. Ananta dkk. (2023) membahas desain aplikasi crowdsourcing pengelolaan sampah di Indonesia.

Kekuatan pendekatan ini ialah warga dapat melaporkan kondisi dekat tempat tinggalnya tanpa menunggu survei lapangan terpusat. Risikonya meliputi koordinat yang kurang tepat, laporan ganda, foto yang tidak cukup menjelaskan kondisi, kategori yang ditafsirkan berbeda, dan laporan yang sudah tidak sesuai saat diperiksa. B-WasteMap menanggapinya dengan foto, koordinat, pilihan kecamatan/jenis/kondisi/bahaya, antrean verifikasi, alasan keputusan, serta pemeriksaan titik terdekat. Namun, sistem tidak otomatis memvalidasi kebenaran foto atau isi laporan.

### 2.4 Riwayat titik dan siklus hidup lokasi

Pada sistem pengaduan biasa, satu laporan sering menjadi satu tiket. B-WasteMap memodelkan objek yang berbeda:

- **Laporan**: kiriman warga pada waktu tertentu.
- **Titik sampah**: lokasi yang dapat dikaitkan dengan beberapa laporan.
- **Penanganan**: pekerjaan yang ditugaskan untuk satu laporan.
- **Riwayat titik**: catatan kejadian yang menghubungkan laporan dan penanganan pada suatu lokasi.

Pemisahan ini memungkinkan sistem mempertahankan konteks lokasi setelah sebuah laporan ditutup. Jika sampah dilaporkan kembali setelah lokasi pernah ditangani, laporan baru dapat ditautkan ke titik lama dan riwayat mencatat kejadian muncul kembali. Model tersebut lebih sesuai untuk mempelajari pola lokasi dibanding menghitung laporan terpisah saja.

Radius 50 meter adalah **parameter rancangan proyek**, bukan standar resmi atau batas yang berlaku universal. Jarak yang cocok dipengaruhi ketelitian GPS, kepadatan permukiman, panjang lokasi pembuangan, dan kondisi lapangan. Karena itu, radius perlu diuji dengan kasus lokasi Banjarmasin. Dalam kode sekarang, hasil pencarian kandidat hanya membantu keputusan; admin tetap memilih tautkan atau buat titik baru. Tidak ada pencocokan otomatis berdasarkan kemiripan gambar.

### 2.5 Posisi terhadap sistem sejenis

Proposal membandingkan B-WasteMap dengan TrashOut, PeduliSampah, e-Sampah, dan Laporia/SP4N-LAPOR!, serta mengulas penelitian aplikasi pengaduan peta di Pekanbaru dan Majalengka serta sistem pendukung keputusan di Banjarmasin (Fitriyadi & Azahra, 2026, hlm. 5–8). Menurut tabel proposal, layanan tersebut menekankan pelaporan sampah, edukasi, transaksi bank sampah, pengaduan publik umum, atau prioritas penanganan. Posisi yang diajukan B-WasteMap ialah mengikat laporan ke riwayat lokasi agar laporan berulang setelah penanganan dapat ditelusuri.

Pembeda itu masuk akal sebagai **posisi desain**. Proposal tidak cukup untuk membuktikan keunikan universal. Pembuktian lebih kuat memerlukan tinjauan publikasi dan produk yang lebih luas, dengan kriteria pembanding yang sama serta tanggal penelusuran yang jelas.

| Pembanding | Fokus yang dicatat proposal | Fokus pembeda B-WasteMap |
| --- | --- | --- |
| TrashOut | Pelaporan komunitas dan pemetaan sampah ilegal | Siklus riwayat titik dalam lingkup Banjarmasin |
| PeduliSampah | Edukasi dan kanal pelaporan lokal | Titik peta interaktif serta riwayat per lokasi |
| e-Sampah | Transaksi/nasabah bank sampah | Sampah liar dan tindak lanjut lokasi |
| Laporia/SP4N-LAPOR! | Pengaduan pelayanan publik lintas topik | Alur khusus dengan koordinat dan penugasan petugas |
| Penelitian pengaduan peta (Fadhli & Putri, 2022; Affan & Handayani, 2025) | Pelaporan dan verifikasi lokasi | Pengaitan kejadian berulang ke titik yang pernah ditangani |
| Penelitian SPK Banjarmasin (Rahman & Raharjo, 2026) | Pemeringkatan prioritas dengan SAW/VIKOR | Fokus pada histori dan keberulangan, bukan skor prioritas |

## 3. Pemetaan Kebutuhan ke Implementasi

Status berikut merujuk pada source code yang diperiksa dan kebutuhan dalam proposal (Fitriyadi & Azahra, 2026, hlm. 9–12), bukan hasil uji penerimaan.

| Kebutuhan | Implementasi yang ditemukan | Status dan batas |
| --- | --- | --- |
| Beranda publik | Landing page berisi ringkasan, alur penggunaan, peta contoh data aktual dari endpoint, dan bukti penanganan terbaru bila tersedia | Ada. Konten bukti berubah mengikuti database |
| Peta publik | Marker Leaflet, status warna, pencarian nomor/alamat, filter status/kecamatan, ringkasan titik, tautan detail | Ada. Endpoint mengirim semua titik dan filter berjalan di browser |
| Lokasi laporan | GPS EXIF dari foto JPG Open Camera, lokasi otomatis ditampilkan pada peta untuk dikonfirmasi | Ada. Metadata diperiksa server saat foto dipilih dan diperiksa ulang saat laporan disimpan; peta tidak mengizinkan koordinat digeser |
| Pelaporan warga | Foto JPG/JPEG maks. 10 MB dengan metadata GPS, konfirmasi tampilan stempel geotag, kecamatan, alamat, jenis, kondisi, potensi bahaya, deskripsi | Ada. Metadata GPS wajib; visibilitas tulisan pada foto dikonfirmasi warga dan tetap perlu diperiksa admin |
| Pelacakan laporan warga | Halaman laporan saya dan nomor laporan BWM-YYYY-#### | Ada. Daftar dipaginasi |
| Verifikasi admin | Antrean laporan; admin menerima atau menolak dengan alasan keputusan | Ada. Laporan yang sudah diproses tidak bisa diproses ulang melalui alur yang sama |
| Pencocokan lokasi | Kandidat dalam 50 m dihitung dan diurutkan menurut jarak; admin memilih titik lama atau titik baru | Ada. Radius tetap di kode dan keputusan akhir manual |
| Penugasan | Admin memilih petugas aktif untuk laporan terverifikasi yang belum ditugaskan | Ada. Pembuatan tugas dan peristiwa riwayat berada dalam transaksi |
| Penanganan petugas | Daftar tugas sendiri, navigasi Google Maps, status, catatan, foto sebelum/sesudah, volume m³ | Ada. Foto sebelum, foto sesudah, dan volume diwajibkan saat status menjadi selesai |
| Riwayat per titik | Timeline kejadian, nomor laporan, status, foto penanganan, volume, hitungan muncul kembali | Ada. Timeline dipaginasi; bukti selesai terbaru diambil terpisah |
| Status pada peta | Status titik berasal dari status tersimpan serta hitungan laporan dan penanganan aktif | Ada. Status tampilan berbeda dari status laporan dan status tugas |
| Dashboard admin | Jumlah titik, antrean verifikasi, penanganan aktif, titik berulang, penyelesaian bulan berjalan, sebaran kecamatan/status, peta monitor | Ada |
| Analitik | Filter tanggal dan kecamatan, agregat bulanan/status, daftar titik berulang, ekspor CSV | Ada. CSV dibatasi ke laporan dalam periode dan kecamatan terpilih |
| Pengguna | Admin dapat membuat akun dan mengubah peran/status; pencarian/filter dan paginasi tersedia | Ada. Tidak ada tombol hapus dari halaman kelola pengguna; penghapusan akun sendiri tersedia di profil dengan beberapa pengaman |
| Target respons 24 jam | Antrean admin menandai laporan yang lebih tua dari 24 jam | Sebagian. Tidak ada bukti SLA operasional, eskalasi, atau notifikasi otomatis |
| Pengaturan radius | Mockup menampilkan aturan kedekatan 50 m | Belum menjadi pengaturan: nilai saat ini konstanta 50 meter di service |
| Fokus pilot Banjarmasin Utara | Form mendukung lima kecamatan Banjarmasin | Cakupan input lebih luas daripada wilayah awal validasi proposal; ini bukan bukti semua kecamatan sudah diuji |

### Istilah dan teknologi yang digunakan

Tabel ini menjelaskan fungsi teknologi di dalam proyek, bukan sekadar kepanjangan istilah. Beberapa istilah yang terdengar mirip bekerja pada lapisan berbeda.

| Istilah | Arti | Pemakaian di B-WasteMap | Batas penting |
| --- | --- | --- | --- |
| Metadata | Data yang menjelaskan data utama: misalnya waktu, perangkat, atau lokasi yang menyertai foto | Foto dapat membawa metadata EXIF; bagian GPS pada metadata itu menjadi sumber koordinat laporan | Metadata dapat hilang, diubah, tidak lengkap, atau tidak benar. Keberadaannya sendiri bukan bukti kebenaran kondisi sampah |
| EXIF | Standar tag metadata yang lazim disimpan di berkas foto kamera | Ekstensi PHP EXIF membaca `GPSLatitude`, `GPSLongitude`, `GPSLatitudeRef`, dan `GPSLongitudeRef` dari JPG/JPEG | Foto yang dikirim ulang lewat aplikasi tertentu dapat kehilangan tag EXIF; proyek meminta foto asli bergeotag |
| Geotag | Informasi lokasi yang dikaitkan dengan foto, biasanya koordinat GPS | Open Camera menyimpan lokasi ke EXIF dan menampilkan stempel lokasi pada gambar; aplikasi membaca tag EXIF dan meminta pelapor mengonfirmasi stempel tampak | Stempel visual dan tag EXIF adalah dua hal terpisah. Sistem saat ini tidak memakai OCR untuk membandingkan keduanya |
| GPS dan koordinat | GPS adalah sistem satelit untuk memperkirakan posisi; koordinat lintang/bujur menyatakan posisi di permukaan bumi | `FotoGeotagService` mengubah derajat-menit-detik EXIF menjadi angka desimal, memakai arah N/S dan E/W untuk tanda koordinat, memeriksa rentang sah, lalu menyimpan sampai tujuh angka desimal | Ketelitian angka desimal database bukan jaminan akurasi GPS lapangan. Perangkat, gedung, cuaca, dan kualitas sinyal berpengaruh |
| Geolocation API | Fitur peramban yang meminta posisi perangkat melalui izin pengguna | Tombol **Lokasi saya** di peta memakai `navigator.geolocation.getCurrentPosition()` untuk memusatkan peta dan menampilkan posisi sementara | Ini berbeda dari EXIF. Posisi tombol peta tidak otomatis dipakai sebagai koordinat laporan; laporan tetap harus berasal dari metadata foto. Peramban umumnya mensyaratkan HTTPS (localhost dikecualikan) dan izin pengguna |
| Web GIS | Sistem informasi geografis yang diakses lewat web/peramban | Warga melihat peta dan mengirim laporan, sementara admin/petugas memakai lokasi yang sama dalam proses operasional | Peta hanya media visualisasi dan navigasi; keputusan tetap perlu bukti serta verifikasi manusia |
| OpenStreetMap (OSM) | Proyek peta dunia kolaboratif yang datanya disumbangkan dan dipelihara komunitas dengan ketentuan lisensi ODbL | Peta dasar mengambil gambar ubin peta (map tiles) dari `tile.openstreetmap.org` dan mencantumkan atribusi kontributor OSM | OSM adalah sumber peta dasar, bukan pustaka JavaScript dan bukan penyedia geometri batas lokal proyek. Layanan tile standar memiliki kebijakan penggunaan dan bukan SLA aplikasi |
| Tile peta | Potongan gambar peta pada tingkat zoom tertentu yang disusun peramban menjadi tampilan peta | Leaflet meminta tile OSM sesuai area dan zoom yang terlihat | Tile OSM menampilkan konteks jalan/tempat; titik sampah dan batas administratif berasal dari data lain |
| Leaflet | Pustaka JavaScript untuk membuat peta interaktif pada halaman web | Leaflet 1.9.4 membuat peta, menambah tile OSM, marker/circle marker, tooltip, kontrol layer, pop-up, dan membaca GeoJSON | Leaflet tidak menyediakan data wilayah atau memverifikasi koordinat; data dan perilaku aplikasi disediakan proyek |
| GeoJSON | Format pertukaran data geografis berbasis JSON, umumnya berisi `FeatureCollection`, `Feature`, `properties`, dan `geometry` | Tiga aset lokal mendeskripsikan batas 52 kelurahan, lima kecamatan, dan batas luar kota. Leaflet memuat dan menggambarkannya sebagai layer | Urutan koordinat GeoJSON adalah `[longitude, latitude]`. File lokal disediakan untuk menggambar wilayah, bukan untuk menentukan posisi laporan atau validasi alamat |
| Feature dan geometry | `Feature` menggabungkan bentuk geografis (`geometry`) dengan atribut (`properties`) | Atribut wilayah menyimpan kode, nama, kecamatan, tingkat administratif, dan titik tengah; geometri menyimpan rangkaian koordinat | Titik tengah hanya membantu penempatan label; bukan titik layanan atau batas wilayah |
| Garis batas turunan | Representasi visual garis luar yang dihitung dari geometri wilayah lebih kecil | Batas kecamatan dan kota pada layer peta dirangkai dari perimeter gabungan geometri kelurahan agar garis bersama sejajar | Aset tersebut untuk visualisasi. Atribusi aset menyatakan data bukan sumber batas hukum tersertifikasi |
| API dan JSON | API adalah antarmuka pertukaran data; JSON adalah format teks untuk mengirim objek dan daftar data | Route `titik-sampah.data` mengirim daftar titik dari database sebagai JSON; JavaScript peta mengambilnya dengan `fetch()` | Endpoint titik saat ini mengirim seluruh titik dan penyaringan dilakukan di browser; pagination/spatial filtering belum diterapkan untuk endpoint ini |
| Laravel, PHP, dan Blade | Laravel adalah kerangka kerja web PHP; Blade adalah sistem templat untuk membentuk HTML halaman | Laravel menangani route, login, validasi, controller, model, database, penyimpanan foto, dan tampilan `.blade.php` | Blade merender sisi server; JavaScript tetap dipakai untuk interaksi peta dan form |
| Eloquent ORM | Lapisan pemetaan objek-relasional Laravel | Model `Laporan`, `TitikSampah`, `Penanganan`, `RiwayatTitik`, dan `User` mendefinisikan akses record serta relasi tabel | ORM tidak menggantikan aturan validasi, kebijakan akses, atau rancangan database |
| MariaDB/MySQL dan migrasi | Database relasional menyimpan tabel; migrasi adalah kode berversi untuk membentuk/mengubah skema | Koordinat, akun, laporan, status tugas, dan timeline disimpan dalam tabel dengan relasi foreign key | File migrasi menyatakan skema yang diminta; status `Ran` hanya menunjukkan migrasi tercatat pada database yang diperiksa, bukan kebenaran seluruh data |
| Haversine | Rumus geometri bola untuk memperkirakan jarak permukaan dari dua koordinat lintang/bujur | `TitikSampahService` menyaring kandidat awal, menghitung jarak, mengurutkannya, dan menerapkan radius rancangan 50 m | Radius 50 m adalah aturan produk yang dipilih proyek, bukan standar geografis atau batas akurasi GPS |
| Autentikasi dan otorisasi | Autentikasi menjawab siapa pengguna; otorisasi menentukan tindakan yang diizinkan | Laravel Breeze mengelola login/daftar/logout dan pemulihan kata sandi; middleware peran membatasi rute admin/petugas; controller memeriksa kepemilikan laporan/tugas/foto | Email verification tersedia sebagai rute, tetapi tidak diwajibkan oleh middleware untuk alur aplikasi saat ini |
| Tailwind CSS, CSS, Alpine.js, dan Vite | Tailwind menyediakan kelas utilitas; CSS mengatur komponen visual; Alpine.js memberi interaksi ringan; Vite mengompilasi aset frontend | Dipakai untuk tampilan responsif, menu navigasi, gaya form, dan bundle CSS/JavaScript aplikasi | Leaflet dimuat sebagai aset eksternal pada tampilan peta; Vite tidak meng-host tile OSM atau batas GeoJSON |
| CSV | Format tabel teks yang dapat dibuka di aplikasi spreadsheet | Admin mengekspor laporan sesuai filter periode dan kecamatan; ekspor bertahap dan teks berawalan formula diberi perlindungan | File ekspor berisi data operasional dan identitas pelapor sehingga perlu dibatasi distribusinya |

#### Cara kerja lokasi dari foto

1. Open Camera memperoleh posisi perangkat dengan layanan lokasi Android setelah izin lokasi diberikan. Pengguna perlu mengaktifkan penyimpanan data lokasi pada foto; stempel yang terlihat di foto juga perlu diaktifkan sesuai kebutuhan bukti visual.
2. Kamera menyimpan koordinat sebagai tag GPS di metadata EXIF JPG. Aplikasi B-WasteMap tidak memperoleh koordinat laporan dari teks stempel pada gambar.
3. Saat foto dipilih, peramban mengirim berkas ke route terautentikasi `laporan.lokasi-foto`. Server PHP membaca tag EXIF, mengubah nilai DMS/rational menjadi derajat desimal, dan mengembalikan lintang-bujur agar form menampilkan lokasinya.
4. Saat laporan dikirim, server membaca EXIF sekali lagi dan menjadikan koordinat hasil baca ulang sebagai nilai simpan. Jika koordinat form berbeda dari metadata foto di luar toleransi yang ditentukan kode, laporan ditolak.
5. Admin tetap perlu memeriksa foto, stempel lokasi yang terlihat, kecamatan, dan titik peta. Pembacaan EXIF menguji format serta konsistensi data, bukan membuktikan foto diambil di tempat yang dinyatakan.

#### Cara kerja peta dan data wilayah

1. Leaflet menyiapkan bidang peta di peramban, lalu meminta tile OSM untuk gambar dasar jalan dan tempat.
2. Route JSON titik sampah mengambil data `titik_sampah` dan ringkasan laporan/penanganan dari MariaDB. JavaScript membuat marker sesuai status, pop-up/detail, jumlah titik, pencarian, dan filter status/kecamatan.
3. Tombol **Lokasi saya** meminta lokasi peramban secara terpisah. Bila izin dan layanan lokasi tersedia, peta berpindah ke posisi perangkat; bila gagal, antarmuka menampilkan pesan. Posisi ini hanya untuk orientasi pada halaman peta.
4. Berkas GeoJSON batas di `public/data/` dimuat dari server proyek sebagai layer kecamatan, kelurahan, dan garis luar kota. GeoJSON tersebut tidak diunduh dari OSM saat peta dibuka.
5. Layer batas dapat ditumpuk dengan tile dan marker, serta diaktifkan/dinonaktifkan melalui kontrol peta. Label ditampilkan menurut tingkat zoom agar tulisan tidak memenuhi peta.

#### Ringkasan aliran teknologi

`Open Camera + layanan lokasi Android` → `JPG dengan EXIF GPS` → `form Blade/JavaScript` → `route Laravel` → `FotoGeotagService/PHP EXIF` → `validasi dan MariaDB` → `API JSON titik` → `Leaflet + tile OSM + layer GeoJSON`.

Alur kerja sesudah laporan berbeda dari alur lokasi: admin memverifikasi dan mengaitkan titik → membuat penugasan → petugas mengubah status serta menyimpan foto/volume → `RiwayatTitik` mencatat kejadian → peta, detail, dashboard, analitik, dan CSV menyajikan data sesuai peran.

### Pemetaan ke mockup

Prototype Project (2).pdf berisi sistem identitas/warna, beranda, peta, beberapa langkah pelaporan, pilihan kandidat titik ±50 m, konfirmasi berhasil, detail riwayat, dashboard/verifikasi/analitik admin, pekerjaan petugas, pengelolaan akun, dan pengaturan. Struktur halaman aktual mencakup sebagian besar alur utama tersebut, dengan tampilan Laravel Blade responsif.

Angka seperti 32 titik, 60 laporan, jumlah petugas, kilometer, dan tanggal pada mockup adalah data ilustratif. DatabaseSeeder.php saat ini hanya membuat tiga akun demo, bukan 32 titik dan data laporan contoh. Tampilan kosong pada database baru karena itu wajar.

## 4. Arsitektur dan Perubahan Data

### 4.1 Alur sistem

~~~mermaid
flowchart LR
    W[Warga: foto, lokasi, rincian] --> L[Laporan menunggu verifikasi]
    L --> A[Admin: periksa bukti dan kandidat 50 m]
    A -->|tautkan| T[Titik lama]
    A -->|titik baru| T2[Titik baru]
    A -->|tolak| R[Alasan penolakan tersimpan]
    T --> P[Admin menugaskan petugas]
    T2 --> P
    P --> O[Petugas memperbarui progres dan bukti]
    O --> H[Riwayat titik bertambah]
    H --> M[Peta, dashboard, analitik]
~~~

### 4.2 Entitas utama

| Tabel | Peran data | Detail yang penting |
| --- | --- | --- |
| users | Akun dan hak akses | Peran admin/petugas/masyarakat serta is_active |
| titik_sampah | Lokasi yang bertahan dari banyak laporan | Latitude/longitude decimal 7 digit, alamat, kecamatan, status, total laporan dan penanganan |
| laporan | Satu kiriman warga | Pelapor, lokasi, foto, jenis/kondisi/bahaya, alasan admin, status, foreign key titik opsional |
| penanganan | Tugas untuk suatu laporan | Petugas, status tugas, foto sebelum/sesudah, volume_m3, catatan, tanggal |
| riwayat_titik | Jejak kejadian sebuah titik | Titik, laporan opsional, status kejadian, keterangan, timestamp |

Migrasi awal membuat pengguna, cache, job, titik, laporan, penanganan, dan riwayat. Migrasi berikutnya menambah alamat/kecamatan/bahaya/alasan, volume, status aktif akun, serta pilihan enum peristiwa seperti laporan ditautkan, penugasan, dan penanganan dimulai. Pada pemeriksaan read-only, semua 10 migrasi terdaftar Ran pada database yang dikonfigurasi.

Relasi memakai foreign key: laporan ikut terhapus ketika akun pelapor dihapus; penanganan ikut terhapus ketika laporan atau akun petugas dihapus; riwayat tetap dapat menunjuk titik dan kolom laporan menjadi null bila laporan dihapus. Perilaku ini perlu dipertimbangkan terhadap kebutuhan mempertahankan audit historis.

### 4.3 Status dan deteksi kedekatan

Status laporan yang tersimpan: menunggu_verifikasi, diverifikasi, ditolak. Status penanganan disimpan terpisah: ditugaskan, dalam_penanganan, selesai. Karena itu, laporan tetap berstatus diverifikasi di tabel laporan setelah pekerjaan selesai; ringkasan status laporan pada analitik bukan ringkasan status pekerjaan. Status titik tersimpan: normal, perlu_perhatian, berulang. Tampilan peta menghitung status efektif seperti dalam_penanganan atau selesai dari tugas dan laporan terkait.

Service pencocokan melakukan penyaringan kotak koordinat agar kandidat yang jauh tidak dihitung satu per satu, lalu menghitung jarak Haversine memakai radius bumi 6.371 km dan membuang kandidat di atas 50 m. Hasil diurutkan dari yang paling dekat. Ketika admin memilih titik lama, server menghitung ulang jarak dan menolak pilihan yang melampaui radius. Verifikasi, penolakan, penugasan, dan perubahan tugas memakai transaksi serta penguncian baris pada beberapa titik penting untuk mencegah pemrosesan ganda.

Catatan definisi metrik: service menulis kejadian muncul_kembali ketika laporan dihubungkan dengan titik yang pernah ditangani atau sudah berstatus berulang. Karena itu, beberapa laporan berdekatan pada satu episode sampah yang sama dapat dihitung sebagai beberapa kejadian muncul kembali. Definisi “satu kemunculan ulang” sebaiknya dipastikan: satu laporan, satu episode sebelum dibersihkan lagi, atau satu siklus setelah penanganan selesai.

## 5. Tambahan dan Penguatan yang Tampak pada Snapshot

Bagian ini mencatat kemampuan yang ada sekarang. Status Git tidak memungkinkan urutan tepat kapan tiap perubahan dibuat.

### Antarmuka dan identitas

- Login berbahasa Indonesia dengan panel identitas, logo putih, formulir email/kata sandi, ingat saya, lupa kata sandi, dan tautan registrasi.
- Navigasi memakai logo utama; favicon memakai logo badge.
- Empat berkas logo ada di public/images: utama, putih, ikon, badge. Layout saat ini menggunakan logo utama, putih, dan badge; logo vertikal dari kumpulan aset pengguna tidak terlihat di folder tersebut.
- CSS memuat palet warna, tombol, field, kartu, navigasi responsif, dan pola dasar antarmuka.
- Landing page terpisah dari peta publik. Detail titik memiliki tautan berbagi yang memakai Web Share API jika tersedia, lalu clipboard sebagai fallback.

### Alur lokasi dan pelaporan

- Lokasi laporan baru berasal dari metadata EXIF GPS pada foto JPG, bukan dari Geolocation API browser. Pengguna perlu mengaktifkan simpan data lokasi pada Open Camera dan izin lokasi untuk kamera.
- Saat foto dipilih, form mengirim salinan sementara ke route terautentikasi `laporan.lokasi-foto`. `FotoGeotagService` membaca GPSLatitude/GPSLongitude dan arah mata angin dari EXIF, lalu mengembalikan koordinat. Server membaca metadata lagi ketika laporan disimpan dan menolak jika foto atau koordinat berubah sejak pemindaian.
- Peta hanya menampilkan pin dari koordinat EXIF agar warga dapat membandingkannya dengan tulisan lokasi yang terlihat pada gambar; pin tidak dapat digeser. Warga harus mengonfirmasi tulisan geotag terlihat dan sesuai. Pemeriksaan visibilitas tersebut belum memakai OCR otomatis, sehingga perlu peninjauan manusia.
- Form dibagi menjadi foto, konfirmasi lokasi, dan keterangan. Foto JPG/JPEG maksimal 10 MB; laporan tanpa metadata GPS yang terbaca tidak dapat dikirim.
- Pengguna dapat memilih lima kecamatan, jenis sampah, kondisi, potensi bahaya, alamat dan deskripsi.
- Nomor laporan dibuat dari tahun dan ID record.

### Operasional admin dan petugas

- Alasan keputusan admin dicatat bersama laporan.
- Kandidat titik diperingkat menurut jarak, bukan dipilih otomatis.
- Petugas hanya dapat memperbarui tugas miliknya; tugas selesai mensyaratkan dua foto dan volume.
- Peristiwa penugasan, mulai penanganan, selesai dibersihkan, laporan ditautkan, dan muncul kembali dicatat pada riwayat.
- Admin dapat mencari/memfilter pengguna dan mengaktifkan/nonaktifkan akun.
- Perlindungan akun mencegah admin aktif terakhir dinonaktifkan/diturunkan perannya/dihapus. Petugas dengan tugas aktif tidak dapat dinonaktifkan, dialihkan peran, atau dihapus melalui jalur profil.
- Antrean admin, laporan warga, tugas petugas, daftar pengguna, dan timeline riwayat menggunakan paginasi.

### Penguatan konsistensi dan ekspor

- File laporan disimpan di disk private/local dan diakses melalui endpoint yang membatasi akses ke admin, pelapor, atau petugas yang ditugaskan.
- Jika simpan foto penanganan berhasil tetapi transaksi database gagal, kode menghapus file yang baru dibuat untuk mengurangi file yatim.
- Ekspor CSV ditulis bertahap per 500 baris, diberi BOM untuk spreadsheet, dan teks yang diawali karakter formula spreadsheet diberi awalan apostrof.
- URL halaman detail titik dibentuk oleh Laravel lalu dikirim ke peta; marker tidak menyusun path secara terpisah.

### Batas administratif peta

- Peta publik `/peta` memuat garis batas lima kecamatan yang dibentuk dengan menggabungkan batas kelurahan, serta garis batas 52 kelurahan yang dapat dinyalakan melalui kontrol layer Leaflet. Batas kecamatan digambar di atas batas kelurahan agar hierarki garis terbaca; label kecamatan terlihat mulai zoom 11 dan label kelurahan mulai zoom 15.
- GeoJSON disimpan lokal di `public/data/wilayah-banjarmasin.geojson` dan `public/data/wilayah-kecamatan-batas.geojson`, sehingga batas wilayah tidak bergantung pada request ke server pihak ketiga saat peta dibuka. Garis kecamatan merupakan hasil penggabungan perimeter kelurahan agar tidak memakai poligon kecamatan sumber yang terlalu disederhanakan. Susunan GeoJSON memakai longitude-latitude; data sumber memberi latitude-longitude dan dikonversi saat aset dibuat.
- Sumber geometri: `cahyadsn/wilayah_boundaries`, snapshot `26fce16d15cc1dc53da79a79359373130cae485d`, berlandaskan Kepmendagri No. 300.2.2-2430 Tahun 2025 sesuai keterangan repositori sumber. Atribusi dan lisensi MIT ada di `public/data/ATTRIBUTION-wilayah.txt`.
- Halaman Bappeda Litbang yang dirujuk berisi tautan situs kecamatan/kelurahan, bukan unduhan batas GIS. Geometri yang digunakan berasal dari sumber batas terpisah dan dipakai untuk visualisasi; bukan penetapan batas hukum. Pembagian wilayah perlu diverifikasi pemilik data sebelum keputusan administratif.
- Layer batas kecamatan aktif saat peta dibuka tanpa isian warna yang menutup peta dasar, sedangkan layer kelurahan tersedia sesuai kebutuhan detail. Filter kecamatan mencantumkan kelima wilayah meskipun belum memiliki titik sampah.

### Lokasi source utama

| Area | Berkas |
| --- | --- |
| Routing dan peran | routes/web.php, routes/auth.php, app/Http/Middleware/RoleMiddleware.php |
| Radius, pengaitan titik, dan riwayat | app/Services/TitikSampahService.php, app/Models/TitikSampah.php, app/Models/RiwayatTitik.php |
| Laporan dan foto warga | app/Http/Controllers/LaporanController.php, resources/views/laporan/create.blade.php, resources/views/laporan/milik-saya.blade.php |
| Verifikasi dan penugasan | app/Http/Controllers/AdminLaporanController.php, app/Http/Controllers/AdminPenangananController.php |
| Pekerjaan petugas | app/Http/Controllers/PetugasController.php, resources/views/petugas/tugas.blade.php |
| Peta dan riwayat publik | app/Http/Controllers/PetaController.php, app/Http/Controllers/TitikSampahController.php, resources/views/peta/index.blade.php, resources/views/titik/show.blade.php |
| Admin dan analitik | app/Http/Controllers/AdminDashboardController.php, app/Http/Controllers/AdminAnalyticsController.php, app/Http/Controllers/AdminUserController.php |
| Skema dan akun awal | database/migrations/, database/seeders/DatabaseSeeder.php |
| Login dan identitas | resources/views/auth/login.blade.php, resources/views/layouts/guest.blade.php, resources/views/layouts/navigation.blade.php, resources/css/app.css, public/images/ |

## 6. Keamanan, Privasi, dan Tata Kelola Data

### Kontrol yang sudah terlihat

- Rute admin dan petugas dipisah dengan middleware autentikasi dan peran.
- Login menerima hanya akun aktif dan membatasi percobaan gagal hingga lima sebelum rate limit.
- Registrasi menetapkan peran dari nilai default akun; pengguna baru tidak mengirim role dari formulir.
- Kata sandi diproses memakai fasilitas hashing Laravel. Rute verifikasi email tersedia, tetapi model User tidak menerapkan MustVerifyEmail dan rute aplikasi memakai auth tanpa middleware verified; verifikasi email tidak diwajibkan sebelum memakai fitur warga.
- Formulir Laravel menggunakan CSRF dan controller memvalidasi koordinat, pilihan kategori, tipe/ukuran foto, tanggal, volume, dan panjang teks.
- Foto laporan asli tidak dipasang langsung sebagai link publik; rute foto memeriksa hak pemilik/admin/petugas.

### Risiko yang perlu keputusan produk

1. **Lokasi persis bersifat publik.** Endpoint peta mengirim koordinat titik dan alamat. Detail titik menampilkan peta lokasi. Data lokasi dapat mengungkap konteks warga atau properti meskipun nama pelapor tidak ditampilkan.
2. **Foto penanganan bersifat publik.** Foto sebelum/sesudah ditayangkan melalui storage publik pada halaman beranda dan detail titik. Tetapkan apakah ini memang kebijakan publik; periksa wajah, plat kendaraan, rumah, metadata foto, dan informasi sensitif sebelum tayang.
3. **Ekspor CSV mengandung data pribadi.** Admin dapat mengekspor nama/email pelapor bersama koordinat, alamat, status dan deskripsi. Batasi akses, jelaskan tujuan, hindari distribusi file secara terbuka, dan tentukan masa penyimpanan.
4. **Penghapusan akun berdampak pada histori.** Foreign key menghapus laporan saat akun warga dihapus dan menghapus tugas saat akun petugas dihapus; berkas foto tidak otomatis ikut dibersihkan di semua jalur. Untuk menjaga histori, pertimbangkan penonaktifan/anonimisasi akun dan penghapusan berkas dengan kebijakan retensi yang eksplisit.
5. **Pemberitahuan privasi belum dibuktikan dalam audit ini.** Jelaskan penggunaan lokasi, tujuan foto, akses petugas, publikasi bukti, ekspor, retensi, dan cara meminta penghapusan/anonimisasi. UU No. 27 Tahun 2022 tentang Pelindungan Data Pribadi relevan sebagai dasar untuk kajian lebih lanjut; bagian ini bukan kesimpulan kepatuhan hukum.
6. **Akun demo hanya untuk lokal.** README dan seeder memakai kata sandi demo sederhana. Ganti/hapus sebelum aplikasi dipublikasikan, sesuai peringatan README.

Rujukan OWASP untuk unggahan berkas menekankan validasi dan pengelolaan penyimpanan. Validasi ekstensi/jenis dan batas 10 MB sudah ada; proses pemindaian, pemrosesan ulang gambar, penghapusan metadata, serta retensi berkas belum ditunjukkan dalam implementasi yang diperiksa.

## 7. Keterbatasan Implementasi dan Risiko Proyek

### Keterbatasan produk

- Radius 50 m hardcoded, belum ada kontrol admin untuk menyesuaikannya seperti yang ditampilkan pada mockup pengaturan.
- Penanda 24 jam tidak mengirim notifikasi atau memastikan petugas merespons tepat waktu.
- Peta publik mengambil semua titik sekaligus. Pencarian dan filter dilakukan di browser; belum tampak pagination, bounding-box API untuk viewport, clustering, atau indeks spasial.
- Koordinat disimpan sebagai decimal, bukan tipe geometri dengan spatial index. Ini cukup untuk prototipe, tetapi perlu pengukuran ketika jumlah titik dan wilayah tumbuh.
- Peta dan Leaflet bergantung pada layanan eksternal OpenStreetMap tile serta CDN Leaflet. Ketersediaan, atribusi, batas penggunaan, kebijakan privasi, dan versi aset perlu diperhatikan sebelum deployment.
- Laporan memerlukan login. Ini mengurangi spam anonim, tetapi menambah hambatan bagi warga; keputusan tersebut perlu diuji pada calon pengguna.
- Lokasi laporan baru memerlukan foto JPG bergeotag dan ekstensi PHP `exif`. Metadata yang bisa dibaca tidak membuktikan tulisan geotag yang tampak pada gambar benar; aplikasi belum menjalankan OCR untuk membandingkan overlay dengan EXIF. Foto serta koordinat tetap perlu diperiksa admin.
- Validasi koordinat hanya memeriksa rentang lintang/bujur global, belum memastikan titik berada di dalam batas administratif Banjarmasin. Kecamatan pilihan dan koordinat karena itu dapat tidak cocok.
- Form menawarkan pilihan kondisi terbatas, tetapi server hanya memvalidasi perkiraan_kondisi sebagai string maksimal 120 karakter; nilai di luar pilihan antarmuka masih dapat dikirim.
- Penelusuran sumber pembanding dalam proposal bertanggal September 2026; perubahan fitur layanan sejak tanggal itu tidak dievaluasi ulang.
- Verifikasi pilihan titik baru tidak mengunci ruang geografis atau menghitung ulang kandidat dalam transaksi. Dua laporan berdekatan yang diverifikasi bersamaan dapat membuat dua record titik untuk lokasi hampir sama.
- Angka analitik Selesai ditangani memilih laporan berdasarkan tanggal laporan dibuat lalu mengecek apakah penanganannya berstatus selesai; angka tersebut bukan jumlah pekerjaan yang selesai pada rentang tanggal terpilih. Label/filter harus menegaskan tanggal dasar, atau query perlu memakai tanggal penanganan.
- Status historis yang dihitung dari status titik saat ini dan event history perlu diuji dengan skenario beberapa laporan dalam satu episode.

### Batas bukti dan pengujian

- Proposal menyebut acceptance, black-box, integration, dan regression testing sebagai pekerjaan roadmap. Pada audit ini, file tes proyek ada, tetapi tes tidak dijalankan.
- Uji GPS perlu dilakukan di perangkat dan browser nyata: izin diberikan/ditolak, akurasi buruk, lokasi tidak tersedia, HTTPS, serta pemilihan manual.
- Belum ada bukti uji usability atau validasi koordinat lapangan di kelima kecamatan. Implementasi kode tidak membuktikan warga memahami alur atau petugas mampu memakai layar di lapangan.
- Data dan foto contoh pada prototipe bukan keluaran database aplikasi.

### Lingkungan dan pengelolaan versi

- Proyek memakai Laravel Framework 13.31 dan membutuhkan PHP minimal 8.3. PHP yang pertama ditemukan di PATH adalah XAMPP PHP 8.2.12; menjalankan Artisan dengan executable itu gagal pada pemeriksaan versi. Laragon menyediakan PHP 8.3.33, dan migrasi berhasil diperiksa dengan executable tersebut. Atur PATH terminal agar memakai PHP Laragon atau gunakan executable Laragon secara eksplisit.
- Status Git di akar C:/laragon/www/b-wastemap menunjukkan file aplikasi saat ini sebagai untracked, sedangkan salinan lama b-wastemap/* ditandai deleted. Sebelum file kajian ini dibuat, status Git memiliki 166 perubahan/entri. Ini membuat perbandingan per commit tidak andal; jangan reset atau staging massal sebelum struktur kerja dipastikan.
- Ada berkas kosong di akar bernama default('masyarakat'). Pastikan asalnya sebelum menghapus.
- Empat logo ada di public/images; varian vertikal tidak tampak digunakan/disalin.

## 8. Rekomendasi Prioritas

### Sebelum demo publik atau deployment

1. Tetapkan pemberitahuan privasi dan batas akses foto. Putuskan secara eksplisit apakah foto sebelum/sesudah, titik berkoordinat persis, alamat, nama petugas, dan deskripsi laporan boleh terlihat publik.
2. Ganti kredensial seeder dan pastikan seeder demo tidak dijalankan pada database produksi.
3. Rapikan struktur Git dan pastikan file proyek berada pada jalur yang memang dilacak sebelum commit. Simpan salinan aman sebelum merapikan status.
4. Jika email terverifikasi menjadi syarat layanan, aktifkan MustVerifyEmail dan middleware verified pada fitur yang memerlukan akun valid.
5. Pastikan terminal memakai PHP Laragon minimal 8.3; dokumentasikan perintah untuk tim.

### Sebelum klaim pengujian selesai

1. Susun test case batas radius: di bawah, tepat pada, dan di atas 50 m; beberapa kandidat; laporan baru; laporan pada titik yang pernah ditangani; serta verifikasi bersamaan untuk koordinat berdekatan.
2. Definisikan unit hitung “muncul kembali” dan uji beberapa laporan untuk satu tumpukan sampah yang sama sebelum penanganan berikutnya.
3. Uji izin peran, akun nonaktif, admin terakhir, petugas dengan tugas aktif, foto invalid/terlalu besar, transaksi gagal setelah unggah, ekspor CSV, dan akses foto.
4. Lakukan uji lapangan terbatas di Banjarmasin Utara bersama warga dan petugas. Catat jumlah peserta, perangkat/browser, waktu menyelesaikan tugas, salah pilih lokasi, kegagalan GPS, serta pemahaman status. Jangan menyimpulkan dampak pengurangan sampah dari sekadar keberhasilan teknis.

### Saat volume pemakaian bertambah

- Terapkan pagination/filter wilayah pada endpoint peta dan evaluasi clustering/index spasial berdasarkan ukuran data aktual.
- Sediakan audit trail keputusan admin dan perubahan akun bila laporan dipakai untuk akuntabilitas operasional.
- Tentukan retensi dan anonimisasi agar penghapusan akun tidak menghapus rekam penanganan yang dibutuhkan.
- Ubah radius menjadi konfigurasi admin hanya jika tim memiliki aturan, otorisasi, dan proses evaluasi yang jelas; jika tidak, pertahankan nilai tetap tetapi dokumentasikan alasan serta batasnya.
- Tinjau kebijakan OSM tile dan CDN; siapkan provider yang sesuai bila aplikasi memerlukan kuota, ketersediaan, atau privasi yang lebih terjamin.

## 9. Kesimpulan

B-WasteMap telah mengimplementasikan mayoritas alur inti yang dirumuskan dalam proposal: pengumpulan laporan berlokasi melalui EXIF foto, verifikasi manusia, pengaitan titik berdasarkan kedekatan, penugasan petugas, bukti penanganan, serta riwayat per titik. Tambahan operasional seperti pengelolaan pengguna, analitik, ekspor CSV, pratinjau koordinat foto, paginasi, validasi, dan perlindungan sebagian operasi juga sudah tampak pada source code.

Kesimpulan yang dapat dipertanggungjawabkan saat ini ialah **MVP tersedia pada tingkat kode dan skema database**. Belum ada cukup bukti untuk menyebut aplikasi teruji di lapangan, memenuhi SLA, aman untuk deployment publik, atau terbukti mengurangi sampah liar. Sebelum menambah fitur besar, prioritas yang paling bernilai ialah menetapkan kebijakan data publik, menguji definisi keberulangan/radius, mengatasi mismatch PHP dan Git, lalu mengumpulkan bukti uji penerimaan warga serta petugas.

## 10. Rujukan

### Literatur dan regulasi

- Affan, M. Z., & Handayani, I. (2025). Perancangan aplikasi pelaporan tempat sampah liar dengan integrasi peta interaktif menggunakan metode Rapid Application Development. TIN: Terapan Informatika Nusantara, 6(6), 795–806. https://doi.org/10.47065/tin.v6i6.8693
- Ananta, M. T., Rohidah, S., Brata, K. C., & Abidin, Z. (2023). Mobile crowdsourcing app design: Managing waste through waste bank in rural area of Indonesia. Proceedings of SIET ’23, 664–672. https://doi.org/10.1145/3626641.3626685
- Brabham, D. C. (2008). Crowdsourcing as a model for problem solving: An introduction and cases. Convergence, 14(1), 75–90. https://doi.org/10.1177/1354856507084420
- Fadhli, M., & Putri, R. A. (2022). Rancang bangun aplikasi pengaduan tempat pembuangan sampah ilegal berbasis map. Technologica, 1, 32–42. https://journals.gesociety.org/index.php/technologica/article/view/31
- Goodchild, M. F. (2007). Citizens as sensors: The world of volunteered geography. GeoJournal, 69, 211–221. https://doi.org/10.1007/s10708-007-9111-y
- Holdi, A., Irwansyah, M. A., & Novriando, H. (2021). Aplikasi WebGIS fasilitas umum menggunakan library Leaflet dan OpenStreetMap. Jurnal Sistem dan Teknologi Informasi, 9(3), 334–341. https://doi.org/10.26418/justin.v9i3.44442
- Kaza, S., Yao, L., Bhada-Tata, P., & Van Woerden, F. (2018). What a Waste 2.0: A Global Snapshot of Solid Waste Management to 2050. World Bank. https://doi.org/10.1596/978-1-4648-1329-0
- Kubásek, M., & Hřebíček, J. (2013). Crowdsource approach for mapping of illegal dumps in the Czech Republic. International Journal of Spatial Data Infrastructures Research, 8, 144–157. https://doi.org/10.2902/1725-0463.2013.08.art7
- Rahman, F., & Raharjo, M. R. (2026). Sistem penunjang keputusan berbasis Google Maps untuk monitoring dan evaluasi titik sampah liar berdasarkan laporan masyarakat. Technologia: Jurnal Ilmiah, 17(1), 178–186. https://doi.org/10.31602/tji.v17i1.21317
- Pemerintah Republik Indonesia. (2008). Undang-Undang Nomor 18 Tahun 2008 tentang Pengelolaan Sampah. https://peraturan.bpk.go.id/Details/39067
- Pemerintah Republik Indonesia. (2022). Undang-Undang Nomor 27 Tahun 2022 tentang Pelindungan Data Pribadi. https://peraturan.bpk.go.id/Details/229798/uu-no-27-tahun-2022

### Dokumen proyek dan konteks lokal

- Fitriyadi, M. Y., & Azahra, N. R. S. (2026). Proposal Proyek Perangkat Lunak B-WasteMap. Dokumen proyek, terutama hlm. 4–12 dan lampiran hlm. 26–27.
- Prototype Project (2).pdf. Prototipe antarmuka B-WasteMap, 22 halaman.
- Pemerintah Kota Banjarmasin. (2025, 25 Februari). TPS liar no way, Banjarmasin bersih-bersih wilayah Selatan. https://www.banjarmasinkota.go.id/2025/02/tps-liar-no-way-banjarmasin-bersih.html
- Dinas Lingkungan Hidup Kota Banjarmasin. (2026, Februari). Seluruh TPS liar Kota Banjarmasin resmi ditutup! https://dlh.banjarmasinkota.go.id/2026/02/seluruh-tps-liar-kota-banjarmasin-resmi.html
- Bappeda Litbang Kota Banjarmasin. (2017). Website Kecamatan dan Kelurahan Se Kota Banjarmasin. https://bappedalitbang.banjarmasinkota.go.id/2017/01/website-kecamatan-dan-kelurahan-se-kota.html
- Cahya DSN. (2026). wilayah_boundaries: data batas wilayah administratif Indonesia [snapshot 26fce16d15cc1dc53da79a79359373130cae485d; lisensi MIT]. https://github.com/cahyadsn/wilayah_boundaries/tree/26fce16d15cc1dc53da79a79359373130cae485d
- B-WasteMap. README.md dan GuideForProject.md. Dokumentasi internal pada folder proyek.

### Dokumentasi teknis

- Laravel 13.x. Validation, authentication, authorization, hashing, filesystem, database migrations. https://laravel.com/docs/13.x
- Leaflet 1.9.4. Reference documentation. https://leafletjs.com/reference.html
- MDN Web Docs. Geolocation API. https://developer.mozilla.org/en-US/docs/Web/API/Geolocation_API
- OpenStreetMap Foundation. Tile Usage Policy. https://operations.osmfoundation.org/policies/tiles/
- OWASP Foundation. File Upload Cheat Sheet. https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html
- OWASP Foundation. CSV Injection. https://owasp.org/www-community/attacks/CSV_Injection
