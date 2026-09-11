# Panduan Membangun Prototype B-WasteMap dari Nol (VS Code)

Panduan ini mengikuti **7 tahap roadmap** yang sudah Anda tulis di proposal (Bab 4.2), ditambah Tahap 0 untuk instalasi tools. Fokusnya adalah membangun **MVP (Minimum Viable Product)** yang membuktikan fitur inti/novelty proposal Anda benar-benar jalan: pemetaan, pelaporan, verifikasi, **deteksi radius ±50m**, dan riwayat titik berulang.

## Daftar Isi

- [Tahap 0 — Instalasi Environment](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-0--instalasi-environment)
- [Tahap 1–2 — Inisialisasi Proyek Laravel](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-12--inisialisasi-proyek-laravel)
- [Tahap 3 — Database & Backend (RBAC)](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-3--database--backend)
- [Tahap 4 — Integrasi Peta (Leaflet + OSM)](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-4--integrasi-peta-leaflet--openstreetmap)
- [Tahap 5 — Histori & Loop (fitur inti)](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-5--histori--loop-fitur-inti-proposal-anda)
- [Checkpoint — routes/web.php Lengkap](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#checkpoint--routesweb-php-lengkap)
- [Tahap 6 — Dashboard & Chart.js](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-6--dashboard--visualisasi)
- [Tahap 7 — Pengujian](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#tahap-7--pengujian)
- [Menjalankan Prototype](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#menjalankan-prototype)
- [Catatan Penyesuaian dari Proposal](https://claude.ai/chat/b70223c3-e2d1-42d7-ae03-884a86b5728b#catatan-penyesuaian-dari-proposal)

---

## Tahap 0 — Instalasi Environment

### 0.1 Visual Studio Code

Download di https://code.visualstudio.com/. Setelah terinstal, buka **Extensions** (`Ctrl+Shift+X`) dan pasang:

- **PHP Intelephense** — autocomplete & error-checking PHP
- **Laravel Blade Formatter** — syntax highlight & format file `.blade.php`
- **Tailwind CSS IntelliSense** — autocomplete class Tailwind
- **DotENV** — syntax highlight file `.env`
- **MySQL** (by Weijan Chen) atau **Database Client** — lihat isi database langsung dari VS Code

### 0.2 PHP, MySQL, Composer — via Laragon (Windows)

Proposal Anda sudah menyebut Laragon di Bab 4.1, jadi kita pakai itu:

1. Download Laragon Full di https://laragon.org/download/ (versi Full sudah termasuk PHP, MySQL, Composer, Node.js).
2. Install lalu jalankan Laragon → klik **Start All** (mengaktifkan Apache + MySQL).
3. Klik kanan ikon Laragon → **PHP** → pastikan versi **8.3 atau lebih baru** terpilih (dibutuhkan Laravel 13, versi terbaru Laravel saat ini).
4. Buka terminal (klik kanan Laragon → **Terminal**, atau pakai terminal bawaan VS Code) lalu cek:
    
    ```bash
    php -vcomposer -Vnode -vnpm -v
    ```
    
    Semua harus mengeluarkan versi (bukan error "command not found").

> **macOS**: pakai [Laravel Herd](https://herd.laravel.com/) (setara Laragon, sekali install langsung dapat PHP + Composer + Nginx). **Linux**: install manual — `sudo apt install php php-cli php-mysql php-mbstring php-xml unzip mysql-server`, lalu Composer dari https://getcomposer.org/download/.

### 0.3 Node.js & npm

Kalau pakai Laragon Full, Node.js sudah termasuk. Kalau belum, download LTS di https://nodejs.org/. Ini dibutuhkan untuk mengompilasi Tailwind CSS.

### 0.4 (Opsional tapi disarankan) Git

Download di https://git-scm.com/ — memudahkan Anda backup progres dan (nanti) push ke GitHub sebagai lampiran skripsi.

---

## Tahap 1–2 — Inisialisasi Proyek Laravel

Analisis kebutuhan & desain ERD/wireframe sudah Anda kerjakan di Bab III proposal — bagian ini kita eksekusi jadi kode.

### 1.1 Buat project baru

Di terminal, arahkan ke folder kerja Anda (misal `www` di Laragon), lalu:

```bash
composer create-project laravel/laravel b-wastemap
cd b-wastemap
```

Perintah ini otomatis mengunduh Laravel versi terbaru (Laravel 13).

### 1.2 Buka di VS Code

```bash
code .
```

Buka terminal terintegrasi di VS Code dengan `Ctrl+` `(backtick) — semua perintah selanjutnya dijalankan dari sini, di dalam folder`b-wastemap`.

### 1.3 Buat database MySQL

Buka `http://localhost/phpmyadmin` (via Laragon) → buat database baru bernama `b_wastemap`. Atau lewat terminal:

```bash
mysql -u root -e "CREATE DATABASE b_wastemap"
```

### 1.4 Konfigurasi `.env`

Buka file `.env` di VS Code, sesuaikan bagian database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=b_wastemap
DB_USERNAME=root
DB_PASSWORD=
```

Tes koneksi dengan menjalankan server dev bawaan Laravel:

```bash
php artisan serve
```

Buka `http://127.0.0.1:8000` di browser — kalau muncul halaman selamat datang Laravel, environment Anda sudah benar. Tekan `Ctrl+C` di terminal untuk stop dulu.

### 1.5 Install autentikasi (Laravel Breeze)

Breeze memberi kita login/register/logout siap pakai, tinggal kita tambah kolom `role`.

```bash
composer require laravel/breeze --dev
php artisan breeze:install blade
npm install
```

---

## Tahap 3 — Database & Backend

### 3.1 Tambahkan kolom `role` ke tabel users

Buka `database/migrations/0001_01_01_000000_create_users_table.php` (dibuat otomatis oleh Laravel), tambahkan satu baris di dalam method `up()`, tepat setelah kolom `password`:

```php
$table->enum('role', ['admin', 'petugas', 'masyarakat'])->default('masyarakat');
```

### 3.2 Migrasi 4 tabel sesuai skema Bab III.4 proposal

Jalankan satu per satu:

```bash
php artisan make:migration create_titik_sampah_table
php artisan make:migration create_laporan_table
php artisan make:migration create_penanganan_table
php artisan make:migration create_riwayat_titik_table
```

**`database/migrations/..._create_titik_sampah_table.php`**

```php
public function up(): void
{
    Schema::create('titik_sampah', function (Blueprint $table) {
        $table->id();
        $table->decimal('latitude', 10, 7);
        $table->decimal('longitude', 10, 7);
        $table->string('alamat')->nullable();
        $table->string('kecamatan')->nullable();
        $table->enum('status', ['normal', 'perlu_perhatian', 'berulang'])->default('normal');
        $table->unsignedInteger('total_laporan')->default(0);
        $table->unsignedInteger('total_penanganan')->default(0);
        $table->timestamps();
    });
}
```

**`..._create_laporan_table.php`**

```php
public function up(): void
{
    Schema::create('laporan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('titik_sampah_id')->nullable()->constrained('titik_sampah')->nullOnDelete();
        $table->decimal('latitude', 10, 7);
        $table->decimal('longitude', 10, 7);
        $table->string('foto');
        $table->text('deskripsi')->nullable();
        $table->string('jenis_sampah')->nullable();
        $table->string('perkiraan_kondisi')->nullable();
        $table->enum('status', ['menunggu_verifikasi', 'diverifikasi', 'ditolak'])->default('menunggu_verifikasi');
        $table->date('tanggal_laporan');
        $table->timestamps();
    });
}
```

**`..._create_penanganan_table.php`**

```php
public function up(): void
{
    Schema::create('penanganan', function (Blueprint $table) {
        $table->id();
        $table->foreignId('laporan_id')->constrained()->cascadeOnDelete();
        $table->foreignId('petugas_id')->constrained('users')->cascadeOnDelete();
        $table->enum('status', ['ditugaskan', 'dalam_penanganan', 'selesai'])->default('ditugaskan');
        $table->string('foto_sebelum')->nullable();
        $table->string('foto_sesudah')->nullable();
        $table->text('catatan')->nullable();
        $table->date('tanggal_penanganan')->nullable();
        $table->timestamps();
    });
}
```

**`..._create_riwayat_titik_table.php`**

```php
public function up(): void
{
    Schema::create('riwayat_titik', function (Blueprint $table) {
        $table->id();
        $table->foreignId('titik_sampah_id')->constrained('titik_sampah')->cascadeOnDelete();
        $table->foreignId('laporan_id')->nullable()->constrained('laporan')->nullOnDelete();
        $table->enum('status_kejadian', ['dilaporkan', 'diverifikasi', 'dibersihkan', 'muncul_kembali']);
        $table->text('keterangan')->nullable();
        $table->timestamps();
    });
}
```

Jalankan migrasi:

```bash
php artisan migrate
```

### 3.3 Buat Model + relasi

```bash
php artisan make:model TitikSampah
php artisan make:model Laporan
php artisan make:model Penanganan
php artisan make:model RiwayatTitik
```

`app/Models/TitikSampah.php`:

```php
class TitikSampah extends Model
{
    protected $table = 'titik_sampah';
    protected $fillable = ['latitude','longitude','alamat','kecamatan','status','total_laporan','total_penanganan'];

    public function laporan() { return $this->hasMany(Laporan::class); }
    public function riwayat() { return $this->hasMany(RiwayatTitik::class)->latest(); }
}
```

`app/Models/Laporan.php`:

```php
class Laporan extends Model
{
    protected $fillable = ['user_id','titik_sampah_id','latitude','longitude','foto','deskripsi','jenis_sampah','perkiraan_kondisi','status','tanggal_laporan'];

    public function pelapor() { return $this->belongsTo(User::class, 'user_id'); }
    public function titikSampah() { return $this->belongsTo(TitikSampah::class); }
    public function penanganan() { return $this->hasOne(Penanganan::class); }
}
```

`app/Models/Penanganan.php`:

```php
class Penanganan extends Model
{
    protected $fillable = ['laporan_id','petugas_id','status','foto_sebelum','foto_sesudah','catatan','tanggal_penanganan'];

    public function laporan() { return $this->belongsTo(Laporan::class); }
    public function petugas() { return $this->belongsTo(User::class, 'petugas_id'); }
}
```

`app/Models/RiwayatTitik.php`:

```php
class RiwayatTitik extends Model
{
    protected $table = 'riwayat_titik';
    protected $fillable = ['titik_sampah_id','laporan_id','status_kejadian','keterangan'];

    public function titikSampah() { return $this->belongsTo(TitikSampah::class); }
    public function laporan() { return $this->belongsTo(Laporan::class); }
}
```

### 3.4 Middleware RBAC (hak akses per peran, Bab 3.1 & 4.3)

```bash
php artisan make:middleware RoleMiddleware
```

`app/Http/Middleware/RoleMiddleware.php`:

```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!$request->user() || !in_array($request->user()->role, $roles)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }
        return $next($request);
    }
}
```

Daftarkan alias-nya di `bootstrap/app.php`:

```php
use Illuminate\Foundation\Configuration\Middleware;

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'role' => \App\Http\Middleware\RoleMiddleware::class,
    ]);
})
```

### 3.5 Symlink storage (agar foto upload bisa diakses browser)

```bash
php artisan storage:link
```

### 3.6 Seeder akun uji coba (biar bisa langsung test 3 peran)

`database/seeders/DatabaseSeeder.php`:

```php
use App\Models\User;

public function run(): void
{
    User::create(['name' => 'Admin', 'email' => 'admin@bwastemap.test', 'password' => bcrypt('password'), 'role' => 'admin']);
    User::create(['name' => 'Petugas 1', 'email' => 'petugas@bwastemap.test', 'password' => bcrypt('password'), 'role' => 'petugas']);
    User::create(['name' => 'Warga', 'email' => 'warga@bwastemap.test', 'password' => bcrypt('password'), 'role' => 'masyarakat']);
}
```

```bash
php artisan db:seed
```

---

## Tahap 4 — Integrasi Peta (Leaflet + OpenStreetMap)

### 4.1 Route + Controller data peta

```bash
php artisan make:controller PetaController
```

`app/Http/Controllers/PetaController.php`:

```php
class PetaController extends Controller
{
    public function index()
    {
        return view('peta.index');
    }

    public function data()
    {
        return response()->json(
            TitikSampah::select('id','latitude','longitude','status','alamat','kecamatan','total_laporan')->get()
        );
    }
}
```

Tambahkan di `routes/web.php`:

```php
use App\Http\Controllers\PetaController;

Route::get('/', [PetaController::class, 'index'])->name('peta');
Route::get('/api/titik-sampah', [PetaController::class, 'data'])->name('titik-sampah.data');
```

### 4.2 View peta publik

Buat `resources/views/peta/index.blade.php`:

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Peta Titik Sampah Liar — Kota Banjarmasin</h2>
    </x-slot>

    <div class="py-6 max-w-6xl mx-auto p-4">
        <p class="mb-4 text-sm">
            <span class="text-green-600">● Normal</span> ·
            <span class="text-yellow-600">● Perlu Perhatian</span> ·
            <span class="text-red-600">● Titik Berulang</span>
        </p>
        <div id="peta" style="height: 600px;"></div>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const map = L.map('peta').setView([-3.3194, 114.5908], 12); // pusat Kota Banjarmasin

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    const warna = { normal: 'green', perlu_perhatian: 'orange', berulang: 'red' };

    fetch("{{ route('titik-sampah.data') }}")
        .then(res => res.json())
        .then(data => data.forEach(titik => {
            L.circleMarker([titik.latitude, titik.longitude], {
                radius: 8, weight: 1, color: '#333',
                fillColor: warna[titik.status] ?? 'gray', fillOpacity: 0.85,
            })
            .bindPopup(`<b>${titik.alamat ?? 'Titik #' + titik.id}</b><br>Status: ${titik.status}<br>Total laporan: ${titik.total_laporan}<br><a href="/titik/${titik.id}">Lihat riwayat →</a>`)
            .addTo(map);
        }));
    </script>
</x-app-layout>
```

> **Catatan:** semua view Blade di proyek ini (bukan hanya yang ini) dibungkus `<x-app-layout>...</x-app-layout>`, **bukan** `@extends('layouts.app')` — itu format khusus Breeze. Judul halaman taruh di `<x-slot name="header">`, isi halaman taruh langsung di dalam tag (tidak perlu `@section`/`@endsection` lagi).

Jalankan `php artisan serve` lalu buka `http://127.0.0.1:8000` — peta akan tampil kosong dulu (wajar, belum ada data titik).

---

## Tahap 5 — Histori & Loop (fitur inti proposal Anda)

Ini bagian **paling penting** — mengimplementasikan alur di Bab 3.2 proposal: _Laporan Masuk → Verifikasi Admin → Cek Radius ±50m → Titik Sama / Titik Baru_.

### 5.1 Form laporan (Masyarakat)

```bash
php artisan make:controller LaporanController
```

`app/Http/Controllers/LaporanController.php`:

```php
class LaporanController extends Controller
{
    public function create()
    {
        return view('laporan.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
            'deskripsi' => 'nullable|string',
            'jenis_sampah' => 'required|string',
        ]);

        $data['foto'] = $request->file('foto')->store('laporan', 'public');
        $data['user_id'] = auth()->id();
        $data['status'] = 'menunggu_verifikasi';
        $data['tanggal_laporan'] = now();

        Laporan::create($data);

        return redirect()->route('laporan.milik-saya')->with('success', 'Laporan terkirim, menunggu verifikasi admin.');
    }

    public function milikSaya()
    {
        $laporan = Laporan::where('user_id', auth()->id())->latest()->get();
        return view('laporan.milik-saya', compact('laporan'));
    }
}
```

`resources/views/laporan/create.blade.php`:

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporkan Titik Sampah Liar</h2>
    </x-slot>

    <div class="py-6 max-w-2xl mx-auto p-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <p class="text-sm mb-2">Klik pada peta untuk menandai lokasi.</p>
        <div id="peta-form" style="height: 400px;" class="mb-4"></div>

        <form method="POST" action="{{ route('laporan.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <input type="hidden" name="latitude" id="latitude">
            <input type="hidden" name="longitude" id="longitude">

            <div>
                <label class="block text-sm">Foto Sampah</label>
                <input type="file" name="foto" accept="image/*" required>
            </div>
            <div>
                <label class="block text-sm">Jenis Sampah</label>
                <select name="jenis_sampah" class="border rounded p-2 w-full" required>
                    <option value="rumah_tangga">Sampah Rumah Tangga</option>
                    <option value="bangunan">Puing Bangunan</option>
                    <option value="lainnya">Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block text-sm">Deskripsi</label>
                <textarea name="deskripsi" class="border rounded p-2 w-full"></textarea>
            </div>
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">Kirim Laporan</button>
        </form>
    </div>

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
    const map = L.map('peta-form').setView([-3.3194, 114.5908], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

    let marker;
    map.on('click', function (e) {
        document.getElementById('latitude').value = e.latlng.lat;
        document.getElementById('longitude').value = e.latlng.lng;
        if (marker) map.removeLayer(marker);
        marker = L.marker(e.latlng).addTo(map);
    });
    </script>
</x-app-layout>
```

`resources/views/laporan/milik-saya.blade.php` (belum pernah ditulis sebelumnya — lengkapi ini juga):

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Laporan Saya</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto p-4">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded mb-4">{{ session('success') }}</div>
        @endif

        <a href="{{ route('laporan.create') }}" class="inline-block bg-green-600 text-white px-4 py-2 rounded mb-4">+ Laporan Baru</a>

        @forelse($laporan as $l)
            <div class="border rounded p-3 mb-3 flex items-center gap-3">
                <img src="{{ asset('storage/'.$l->foto) }}" class="w-16 h-16 object-cover rounded">
                <div>
                    <div>{{ $l->jenis_sampah }} — {{ $l->deskripsi }}</div>
                    <span class="text-xs px-2 py-1 rounded bg-gray-200">{{ $l->status }}</span>
                </div>
            </div>
        @empty
            <p class="text-gray-500">Belum ada laporan.</p>
        @endforelse
    </div>
</x-app-layout>
```

Tambahkan route (di dalam grup `auth`):

```php
use App\Http\Controllers\LaporanController;

Route::middleware('auth')->group(function () {
    Route::get('/laporan/buat', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/saya', [LaporanController::class, 'milikSaya'])->name('laporan.milik-saya');
});
```

### 5.2 Service deteksi radius (jantung dari novelty proposal Anda)

> **Catatan penting:** project Anda pakai _optimized autoloader_ Composer. Artinya setiap kali Anda membuat file class PHP baru **secara manual** (seperti Service ini — bukan lewat `php artisan make:...`), Composer tidak otomatis mendeteksinya. Selalu jalankan `composer dump-autoload` setelah membuat file class baru secara manual, atau Anda akan kena error "Target class ... does not exist".

Buat folder `Services` di dalam `app` — lewat VS Code (klik kanan folder `app` → New Folder → beri nama `Services`) atau lewat terminal:

```bash
mkdir app\Services
```

(bukan `mkdir -p app/Services` — flag `-p` gaya Linux/Mac itu kadang tidak dikenali di CMD/PowerShell Windows)

`app/Services/TitikSampahService.php`:

```php
namespace App\Services;

use App\Models\Laporan;
use App\Models\TitikSampah;
use App\Models\RiwayatTitik;
use Illuminate\Support\Facades\DB;

class TitikSampahService
{
    const RADIUS_METER = 50;

    public function prosesVerifikasi(Laporan $laporan): TitikSampah
    {
        $titikTerdekat = $this->cariTitikDalamRadius($laporan->latitude, $laporan->longitude);

        if ($titikTerdekat) {
            $titikTerdekat->increment('total_laporan');
            $titik = $titikTerdekat;
            $statusKejadian = 'muncul_kembali';
            $keterangan = 'Laporan baru masuk pada titik yang sudah pernah tercatat.';
        } else {
            $titik = TitikSampah::create([
                'latitude' => $laporan->latitude,
                'longitude' => $laporan->longitude,
                'status' => 'normal',
                'total_laporan' => 1,
                'total_penanganan' => 0,
            ]);
            $statusKejadian = 'dilaporkan';
            $keterangan = 'Titik baru dibuat dari laporan pertama.';
        }

        $laporan->update(['titik_sampah_id' => $titik->id, 'status' => 'diverifikasi']);

        RiwayatTitik::create([
            'titik_sampah_id' => $titik->id,
            'laporan_id' => $laporan->id,
            'status_kejadian' => $statusKejadian,
            'keterangan' => $keterangan,
        ]);

        $this->perbaruiStatusKeberulangan($titik);

        return $titik;
    }

    private function cariTitikDalamRadius(float $lat, float $lng): ?TitikSampah
    {
        // Formula Haversine: jarak antar 2 koordinat di permukaan bumi (dalam meter)
        $sql = "
            SELECT id, (
                6371000 * acos(
                    cos(radians(?)) * cos(radians(latitude)) *
                    cos(radians(longitude) - radians(?)) +
                    sin(radians(?)) * sin(radians(latitude))
                )
            ) AS jarak
            FROM titik_sampah
            HAVING jarak <= ?
            ORDER BY jarak ASC
            LIMIT 1
        ";

        $hasil = collect(DB::select($sql, [$lat, $lng, $lat, self::RADIUS_METER]))->first();

        return $hasil ? TitikSampah::find($hasil->id) : null;
    }

    private function perbaruiStatusKeberulangan(TitikSampah $titik): void
    {
        $status = match (true) {
            $titik->total_laporan > 3 => 'berulang',
            $titik->total_laporan >= 2 => 'perlu_perhatian',
            default => 'normal',
        };
        $titik->update(['status' => $status]);
    }
}
```

Setelah file ini tersimpan, **langsung jalankan**:

```bash
composer dump-autoload
```

Ini persis menerjemahkan tabel kategori di Bab 3.2 proposal Anda: **Normal** (0–1 laporan), **Perlu Perhatian** (2–3), **Titik Berulang** (>3).

### 5.3 Panel Admin — verifikasi & penugasan (satu layar)

```bash
php artisan make:controller AdminLaporanController
```

`app/Http/Controllers/AdminLaporanController.php`:

```php
use App\Models\User;
use App\Services\TitikSampahService;

class AdminLaporanController extends Controller
{
    public function index()
    {
        $laporanBaru = Laporan::where('status', 'menunggu_verifikasi')->latest()->get();

        $laporanDiverifikasi = Laporan::where('status', 'diverifikasi')
            ->whereDoesntHave('penanganan')
            ->latest()
            ->get();

        $petugas = User::where('role', 'petugas')->get();

        return view('admin.laporan.index', compact('laporanBaru', 'laporanDiverifikasi', 'petugas'));
    }

    public function verifikasi(Laporan $laporan, TitikSampahService $service)
    {
        $service->prosesVerifikasi($laporan);
        return back()->with('success', 'Laporan diverifikasi & dikaitkan ke titik lokasi.');
    }

    public function tolak(Laporan $laporan)
    {
        $laporan->update(['status' => 'ditolak']);
        return back()->with('success', 'Laporan ditolak.');
    }
}
```

> Catatan: halaman ini digabung dengan penugasan petugas (lihat `$laporanDiverifikasi` & `$petugas`) — laporan yang sudah diverifikasi tapi belum ada `Penanganan`-nya langsung bisa ditugaskan dari layar yang sama, tanpa perlu berpindah halaman.

Route (grup admin, dilindungi middleware `role`):

```php
use App\Http\Controllers\AdminLaporanController;

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('admin.laporan.index');
    Route::post('/laporan/{laporan}/verifikasi', [AdminLaporanController::class, 'verifikasi'])->name('admin.laporan.verifikasi');
    Route::post('/laporan/{laporan}/tolak', [AdminLaporanController::class, 'tolak'])->name('admin.laporan.tolak');
});
```

`resources/views/admin/laporan/index.blade.php` (satu layar: verifikasi + penugasan):

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Kelola Laporan</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto p-4 space-y-8">
        @if(session('success'))
            <div class="bg-green-100 text-green-700 p-3 rounded">{{ session('success') }}</div>
        @endif

        <div>
            <h3 class="font-semibold text-lg mb-3">Menunggu Verifikasi</h3>
            @forelse($laporanBaru as $l)
                <div class="border rounded p-3 mb-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/'.$l->foto) }}" class="w-20 h-20 object-cover rounded">
                        <div>{{ $l->jenis_sampah }} — {{ $l->deskripsi }}</div>
                    </div>
                    <div class="space-x-2">
                        <form method="POST" action="{{ route('admin.laporan.verifikasi', $l) }}" class="inline">
                            @csrf
                            <button class="bg-green-600 text-white px-3 py-1 rounded">Verifikasi</button>
                        </form>
                        <form method="POST" action="{{ route('admin.laporan.tolak', $l) }}" class="inline">
                            @csrf
                            <button class="bg-red-600 text-white px-3 py-1 rounded">Tolak</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Tidak ada laporan baru.</p>
            @endforelse
        </div>

        <div>
            <h3 class="font-semibold text-lg mb-3">Sudah Diverifikasi — Belum Ditugaskan</h3>
            @forelse($laporanDiverifikasi as $l)
                <div class="border rounded p-3 mb-3 flex justify-between items-center">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('storage/'.$l->foto) }}" class="w-20 h-20 object-cover rounded">
                        <div>{{ $l->jenis_sampah }} — {{ $l->deskripsi }}</div>
                    </div>
                    <form method="POST" action="{{ route('admin.penanganan.tugaskan', $l) }}" class="flex items-center gap-2">
                        @csrf
                        <select name="petugas_id" class="border rounded p-1" required>
                            <option value="">Pilih petugas</option>
                            @foreach($petugas as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        <button class="bg-blue-600 text-white px-3 py-1 rounded">Tugaskan</button>
                    </form>
                </div>
            @empty
                <p class="text-gray-500">Tidak ada laporan yang menunggu penugasan.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
```

### 5.4 Penugasan & pekerjaan Petugas Lapangan

```bash
php artisan make:controller PetugasController
```

`app/Http/Controllers/PetugasController.php`:

```php
class PetugasController extends Controller
{
    public function index()
    {
        $tugas = Penanganan::where('petugas_id', auth()->id())
            ->whereIn('status', ['ditugaskan', 'dalam_penanganan'])
            ->with('laporan.titikSampah')
            ->get();
        return view('petugas.tugas', compact('tugas'));
    }

    public function update(Request $request, Penanganan $penanganan)
    {
        $data = $request->validate([
            'status' => 'required|in:dalam_penanganan,selesai',
            'foto_sebelum' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'foto_sesudah' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'catatan' => 'nullable|string',
        ]);

        if ($request->hasFile('foto_sebelum')) {
            $data['foto_sebelum'] = $request->file('foto_sebelum')->store('penanganan', 'public');
        }
        if ($request->hasFile('foto_sesudah')) {
            $data['foto_sesudah'] = $request->file('foto_sesudah')->store('penanganan', 'public');
        }

        $penanganan->update($data);

        if ($data['status'] === 'selesai') {
            $penanganan->update(['tanggal_penanganan' => now()]);
            $penanganan->laporan->titikSampah->increment('total_penanganan');

            RiwayatTitik::create([
                'titik_sampah_id' => $penanganan->laporan->titik_sampah_id,
                'laporan_id' => $penanganan->laporan_id,
                'status_kejadian' => 'dibersihkan',
                'keterangan' => $data['catatan'] ?? 'Penanganan selesai dilakukan.',
            ]);
        }

        return back()->with('success', 'Status penanganan diperbarui.');
    }
}
```

Admin perlu bisa menugaskan petugas ke laporan yang sudah diverifikasi — tambahkan controller kecil:

```bash
php artisan make:controller AdminPenangananController
```

```php
class AdminPenangananController extends Controller
{
    public function tugaskan(Request $request, Laporan $laporan)
    {
        $request->validate(['petugas_id' => 'required|exists:users,id']);
        Penanganan::create([
            'laporan_id' => $laporan->id,
            'petugas_id' => $request->petugas_id,
            'status' => 'ditugaskan',
        ]);
        return back()->with('success', 'Petugas ditugaskan.');
    }
}
```

Routes tambahan:

```php
use App\Http\Controllers\{AdminPenangananController, PetugasController};

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::post('/laporan/{laporan}/tugaskan', [AdminPenangananController::class, 'tugaskan'])->name('admin.penanganan.tugaskan');
});

Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->group(function () {
    Route::get('/tugas', [PetugasController::class, 'index'])->name('petugas.tugas');
    Route::post('/tugas/{penanganan}/update', [PetugasController::class, 'update'])->name('petugas.tugas.update');
});
```

### 5.5 Halaman riwayat/detail titik (timeline)

```bash
php artisan make:controller TitikSampahController
```

```php
class TitikSampahController extends Controller
{
    public function show(TitikSampah $titikSampah)
    {
        $riwayat = $titikSampah->riwayat()->with('laporan.penanganan')->get();
        return view('titik.show', compact('titikSampah', 'riwayat'));
    }
}
```

```php
Route::get('/titik/{titikSampah}', [App\Http\Controllers\TitikSampahController::class, 'show'])->name('titik.show');
```

`resources/views/titik/show.blade.php` (ringkas, sesuai contoh tata letak Bab 3.5):

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Riwayat Titik #{{ $titikSampah->id }}</h2>
    </x-slot>

    <div class="py-6 max-w-3xl mx-auto p-4">
        <p class="mb-1">{{ $titikSampah->alamat ?? 'Tanpa alamat' }}</p>
        <p class="mb-4">Status: <b>{{ $titikSampah->status }}</b> | Total laporan: {{ $titikSampah->total_laporan }} | Total penanganan: {{ $titikSampah->total_penanganan }}</p>

        <h2 class="font-semibold mt-4 mb-2">Riwayat (terbaru di atas)</h2>
        <ul class="border-l-2 pl-4 space-y-3">
            @foreach($riwayat as $r)
                <li>
                    <b>{{ $r->status_kejadian }}</b> — {{ $r->created_at->format('d M Y H:i') }}<br>
                    <span class="text-sm text-gray-600">{{ $r->keterangan }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</x-app-layout>
```

---

## Checkpoint — routes/web.php Lengkap

Sampai titik ini, route sudah ditambahkan sedikit-sedikit di Tahap 4 dan 5. Supaya tidak ada yang tertinggal/salah taruh, **timpa seluruh isi `routes/web.php`** dengan versi gabungan ini:

```php
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminLaporanController;
use App\Http\Controllers\AdminPenangananController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\TitikSampahController;

// Halaman publik (tanpa login)
Route::get('/', [PetaController::class, 'index'])->name('peta');
Route::get('/api/titik-sampah', [PetaController::class, 'data'])->name('titik-sampah.data');
Route::get('/titik/{titikSampah}', [TitikSampahController::class, 'show'])->name('titik.show');

// Dashboard bawaan Breeze — JANGAN dihapus, dipakai link logo di navbar
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Wajib login, berlaku untuk semua role
Route::middleware('auth')->group(function () {
    Route::get('/laporan/buat', [LaporanController::class, 'create'])->name('laporan.create');
    Route::post('/laporan', [LaporanController::class, 'store'])->name('laporan.store');
    Route::get('/laporan/saya', [LaporanController::class, 'milikSaya'])->name('laporan.milik-saya');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Khusus admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/laporan', [AdminLaporanController::class, 'index'])->name('admin.laporan.index');
    Route::post('/laporan/{laporan}/verifikasi', [AdminLaporanController::class, 'verifikasi'])->name('admin.laporan.verifikasi');
    Route::post('/laporan/{laporan}/tolak', [AdminLaporanController::class, 'tolak'])->name('admin.laporan.tolak');
    Route::post('/laporan/{laporan}/tugaskan', [AdminPenangananController::class, 'tugaskan'])->name('admin.penanganan.tugaskan');
});

// Khusus petugas
Route::middleware(['auth', 'role:petugas'])->prefix('petugas')->group(function () {
    Route::get('/tugas', [PetugasController::class, 'index'])->name('petugas.tugas');
    Route::post('/tugas/{penanganan}/update', [PetugasController::class, 'update'])->name('petugas.tugas.update');
});

require __DIR__.'/auth.php';
```

Verifikasi tidak ada route yang bentrok/hilang:

```bash
php artisan route:list
```

Anda harus melihat semua nama route di atas muncul di kolom **Name**, tanpa error saat perintah dijalankan.

> **Ingat:** `LaporanController@create()` memanggil `view('laporan.create')` — file blade-nya **wajib** ada di `resources/views/laporan/create.blade.php` (bukan di `resources/views/admin/laporan/create.blade.php`). Begitu juga `laporan.milik-saya` → `resources/views/laporan/milik-saya.blade.php`. Kalau Anda sempat membuatnya di folder `admin/laporan/`, pindahkan filenya ke `resources/views/laporan/` (buat foldernya kalau belum ada).

---

## Tahap 6 — Dashboard & Visualisasi

```bash
php artisan make:controller AdminDashboardController
```

```php
class AdminDashboardController extends Controller
{
    public function index()
    {
        $totalTitik = TitikSampah::count();
        $laporanBaru = Laporan::where('status', 'menunggu_verifikasi')->count();
        $dalamPenanganan = Penanganan::where('status', '!=', 'selesai')->count();
        $titikBerulang = TitikSampah::where('status', 'berulang')->count();
        $perKecamatan = TitikSampah::selectRaw('kecamatan, count(*) as jumlah')->groupBy('kecamatan')->pluck('jumlah', 'kecamatan');
        $perStatus = TitikSampah::selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return view('admin.dashboard', compact('totalTitik','laporanBaru','dalamPenanganan','titikBerulang','perKecamatan','perStatus'));
    }
}
```

```php
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\AdminDashboardController::class, 'index'])->name('admin.dashboard');
});
```

`resources/views/admin/dashboard.blade.php`:

```html
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard Admin</h2>
    </x-slot>

    <div class="py-6 max-w-5xl mx-auto p-4">
        <div class="grid grid-cols-4 gap-4 mb-6">
            <div class="p-4 bg-white rounded shadow text-center"><div class="text-2xl font-bold">{{ $totalTitik }}</div>Total Titik</div>
            <div class="p-4 bg-white rounded shadow text-center"><div class="text-2xl font-bold">{{ $laporanBaru }}</div>Laporan Baru</div>
            <div class="p-4 bg-white rounded shadow text-center"><div class="text-2xl font-bold">{{ $dalamPenanganan }}</div>Dalam Penanganan</div>
            <div class="p-4 bg-white rounded shadow text-center"><div class="text-2xl font-bold">{{ $titikBerulang }}</div>Titik Berulang</div>
        </div>
        <canvas id="chartKecamatan" class="mb-6"></canvas>
        <canvas id="chartStatus"></canvas>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    new Chart(document.getElementById('chartKecamatan'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($perKecamatan->keys()) !!},
            datasets: [{ label: 'Titik per Kecamatan', data: {!! json_encode($perKecamatan->values()) !!}, backgroundColor: '#16a34a' }]
        }
    });
    new Chart(document.getElementById('chartStatus'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($perStatus->keys()) !!},
            datasets: [{ data: {!! json_encode($perStatus->values()) !!}, backgroundColor: ['#22c55e','#eab308','#ef4444'] }]
        }
    });
    </script>
</x-app-layout>
```

---

## Tahap 7 — Pengujian

Sesuai Bab 4.2 poin 7 (pengujian black-box), cek manual alur ini secara berurutan:

1. **Registrasi & login** sebagai masyarakat → berhasil masuk.
2. **Kirim laporan** dengan foto + titik di peta → muncul di `laporan/saya` berstatus _menunggu verifikasi_.
3. **Login sebagai admin** → laporan muncul di `admin/laporan`.
4. **Verifikasi laporan pertama** di suatu koordinat → cek di database: `titik_sampah` baru terbuat, `riwayat_titik` tercatat `dilaporkan`.
5. **Kirim laporan kedua** dari akun masyarakat lain di koordinat **dalam radius 50m** dari titik tadi → verifikasi lagi → pastikan **tidak** membuat titik baru, tapi `total_laporan` pada titik lama bertambah dan statusnya berubah jadi `perlu_perhatian` (jika sudah 2 laporan).
6. **Tugaskan petugas**, login sebagai petugas, update status ke `selesai` dengan foto sebelum/sesudah → cek `total_penanganan` bertambah dan `riwayat_titik` mencatat `dibersihkan`.
7. **Buka halaman peta publik** → marker berubah warna sesuai status.
8. **Buka dashboard admin** → angka & grafik sesuai data yang sudah dibuat.

Opsional — test otomatis untuk logika radius (bagian paling kritis):

```bash
php artisan make:test TitikSampahServiceTest
```

```php
public function test_laporan_dalam_radius_masuk_ke_titik_yang_sama(): void
{
    $titik = TitikSampah::factory()->create(['latitude' => -3.3194, 'longitude' => 114.5908, 'total_laporan' => 1]);
    $laporan = Laporan::factory()->create(['latitude' => -3.3195, 'longitude' => 114.5909]); // ~15m dari titik di atas

    app(TitikSampahService::class)->prosesVerifikasi($laporan);

    $this->assertEquals(1, TitikSampah::count()); // tidak membuat titik baru
    $this->assertEquals(2, $titik->fresh()->total_laporan);
}
```

(Butuh factory untuk `TitikSampah` dan `Laporan` — `php artisan make:factory TitikSampahFactory` dst.)

---

## Menjalankan Prototype

Dua terminal terpisah di VS Code (split terminal dengan ikon `+`):

```bash
# Terminal 1 — server Laravel
php artisan serve

# Terminal 2 — compile Tailwind/assets
npm run dev
```

Buka `http://127.0.0.1:8000`, login dengan akun seeder tadi (`admin@bwastemap.test` / `petugas@bwastemap.test` / `warga@bwastemap.test`, password `password`).

---

## Catatan Penyesuaian dari Proposal

Supaya jujur dan bisa Anda jelaskan kalau ditanya dosen pembimbing/penguji:

1. **Kolom `latitude`/`longitude` ditambahkan ke tabel `laporan`.** Skema di Bab 3.4 proposal tidak mencantumkannya, tapi diperlukan secara teknis: sistem butuh tahu koordinat laporan **sebelum** admin memutuskan apakah itu titik lama atau baru (sesuai alur di Bab 3.2). Ini konsisten dengan alur, hanya melengkapi detail implementasi skema.
2. **Nilai status memakai underscore** (`perlu_perhatian`, `dalam_penanganan`, `muncul_kembali`) alih-alih spasi — batasan teknis enum MySQL, maknanya identik dengan istilah di proposal Anda.
3. Fitur di luar cakupan (AI/Computer Vision, SPK SAW/VIKOR, gateway WhatsApp/SMS, navigasi rute armada) **sengaja tidak diimplementasikan**, sesuai Batasan Proyek Bab 1.5 — jadi prototype ini scope-nya sudah tepat sesuai proposal, tidak kurang tidak lebih.

Kalau nanti Anda sudah sampai sejauh ini dan ingin lanjut ke bagian tertentu (misalnya styling UI yang lebih rapi, deploy ke hosting, atau menambah fitur ekspor laporan), tinggal lanjutkan saja dari sini.