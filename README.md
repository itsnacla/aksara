# AKSARA

Aksara adalah sistem manajemen sekolah yang dirancang untuk menjadi pusat data pendidikan yang dinamis, akurat, dan transparan. Proyek ini menggabungkan kekuatan **Filament PHP** untuk manajemen data tingkat tinggi dengan **Portal Kustom** yang intuitif bagi siswa dan orang tua.

---

## 🚀 Fitur Utama

-   **QR Attendance & WA Notification**: Sistem absensi berbasis QR Code yang secara otomatis mengirimkan notifikasi WhatsApp ke orang tua/wali murid secara real-time.
-   **AI-Powered Architecture**: Menggunakan PostgreSQL dengan ekstensi **PG Vector** untuk mempersiapkan fitur AI masa depan seperti pencarian semantik dan analisis data pendidikan yang cerdas.
-   **Hybrid Authentication Flow**: Sistem cerdas yang mendeteksi peran pengguna secara otomatis dan mengarahkan mereka ke dasbor yang sesuai (Filament Panel untuk Admin/Guru vs Custom Portal untuk Siswa/Siswa).
-   **Manajemen Akademik Terintegrasi**: Pengaturan Tahun Ajaran, Semester, dan Tingkatan Kelas yang fleksibel.
-   **RBAC (Role-Based Access Control)**: Menggunakan Filament Shield untuk pembatasan akses yang ketat (Admin, Guru, Staff, Siswa, Wali).
-   **Manajer Relasi Siswa & Wali**: Koneksi otomatis antara data siswa dengan profil orang tua mereka.
-   **Penugasan Wali Kelas**: Sistem penugasan guru ke kelas dengan validasi otomatis.
-   **Antarmuka Premium**: Menggunakan Tailwind CSS 4 dan Filament v3/v5 untuk pengalaman pengguna yang modern dan cepat.

---

## 🌟 Pembaruan & Peningkatan Sistem Terkini

-   **Pusat Kendali WhatsApp Gateway Terintegrasi**: Halaman manajemen **WA Notifikasi** mandiri di dalam panel Filament yang mendukung multi-provider (Fonnte & Custom API Gateway) lengkap dengan kustomisasi parameter dan token otorisasi.
-   **Notifikasi Absensi Latar Belakang (Real-time Queued)**: Pengiriman otomatis pesan WhatsApp terformat rapi ke orang tua/wali murid saat siswa memindai kartu presensi (masuk/pulang). Dilengkapi dengan penamaan sekolah dinamis pada *header* dan *branding* elegan (`Powered by Aksara | Tateta`) pada *footer*, diproses asinkron di latar belakang (*Job/Queue*) tanpa membebani kecepatan pemindaian.
-   **Pemindai QR Mandiri Anti-Duplikasi (Kiosk Mode)**: Modul pemindai presensi mandiri yang dapat diakses di tab terpisah untuk mencegah bentrok elemen Livewire. Dilengkapi dengan proteksi ganda berupa **Cooldown Klien (5 detik)** dan **Cooldown Server (10 detik)** guna mengatasi *race condition*, serta memprioritaskan privasi siswa dengan menghapus *fallback* avatar pihak ketiga.
-   **Mesin Siaran Pengumuman (Broadcast Engine)**: Memungkinkan staf atau pengelola sekolah untuk mengirimkan pengumuman penting secara massal ke seluruh orang tua, maupun difilter spesifik berdasarkan **Rombel/Kelas** tertentu.
-   **Alih Sesi Mandiri Premium (Login As / User Impersonation)**: Fitur peniruan identitas pengguna (*impersonation*) 100% native bawaan Filament yang diakses langsung melalui menu profil pengguna (khusus super admin). Dilengkapi pengalihan peran otomatis (Portal `/dashboard` untuk siswa/wali, Panel Admin `/admin` untuk guru/staf), serta tombol pengembalian sesi instan ke admin asli.
-   **Arsitektur Wilayah Terdistribusi (TatetaGeo Microservice)**: Pemindahan mesin pencarian data wilayah Indonesia ke mikroservis eksternal **TatetaGeo** berbasis Laravel 13 & SQLite yang terproteksi API Sanctum, lengkap dengan monitor status kesehatan live di dasbor dan **failover otomatis (dynamic fallback) ke Emsifa CDN** jika server mati.

---

## 🛠️ Tech Stack

| Komponen            | Teknologi              | Versi    |
| ------------------- | ---------------------- | -------- |
| **Framework**       | Laravel                | 13.x     |
| **Admin Panel**     | Filament PHP           | ~5.0     |
| **Database**        | PostgreSQL (PG Vector) | 16+      |
| **Styling**         | Tailwind CSS           | 4.0      |
| **RBAC**            | Filament Shield        | ^4.2     |
| **Runtime**         | PHP                    | 8.4+     |
| **Dev Tool**        | Laravel IDE Helper     | ^3.7     |

---

## 📊 MVC Flow Chart (Basic)

![flowchat simple v1](image.png)

---

## ⚙️ Instalasi & Setup Lengkap

Ikuti langkah-langkah di bawah ini untuk menjalankan Aksara di lingkungan lokal Anda. Pastikan sistem Anda memenuhi **Requirement Minimum: PHP 8.4, Node 20+, & PostgreSQL 16**.

### 1. Kloning & Instalasi
Dapatkan kode sumber dan instal semua dependensi yang diperlukan:

```bash
# Clone repository
git clone https://github.com/itsnacla/Aksara.git
cd Aksara

# Metode A: Setup Otomatis (Direkomendasikan)
composer setup

# Metode B: Instalasi Manual
composer install
npm install
```

### 2. Konfigurasi Environment (`.env`)
Salin file environment dan buat Application Key:

```bash
cp .env.example .env
php artisan key:generate
```

> [!IMPORTANT]
> Buka file `.env` dan sesuaikan bagian database:
> `DB_CONNECTION=pgsql`, `DB_DATABASE=nama_db`, `DB_USERNAME=postgres`, `DB_PASSWORD=password`.

### 3. Aktivasi PG Vector (Krusial)
Aksara membutuhkan ekstensi **pgvector** untuk fitur AI. Pastikan ekstensi ini diaktifkan di PostgreSQL Anda:

```sql
-- Jalankan di SQL Console / pgAdmin
CREATE EXTENSION IF NOT EXISTS vector;
```

### 4. Link Storage & Filament Assets
Langkah ini wajib agar UI Filament dan file upload (avatar/media) tampil dengan benar:

```bash
# Menghubungkan storage (untuk media/upload)
php artisan storage:link

# Re-publish assets Filament terbaru
php artisan filament:assets
php artisan filament:upgrade
```

### 5. Inisialisasi Security & Data Demo
Bangun skema database dan jalankan seeder utama yang mencakup *Master Data*, *Waktu/Jam Pelajaran*, *Peran*, dan data sampel:

```bash
# Fresh migration dan jalankan seluruh seeder otomatis
php artisan migrate:fresh --seed

# Generate permissions & policies (Filament Shield)
php artisan shield:generate --all --panel=admin --no-interaction
```

### 6. Integrasi TatetaGeo (Location Intelligence Service)
Aksara terintegrasi secara mulus dengan **TatetaGeo**—sebuah layanan *Location Intelligence* berbasis mikroservis terpisah yang menyajikan data wilayah administrasi Indonesia secara lokal dan cepat.

Cukup konfigurasikan variabel berikut pada file `.env` Aksara Anda untuk menghubungkan ke instance TatetaGeo Anda:
- `TATETA_GEO_URL=http://127.0.0.1:8001` (URL/Port tempat TatetaGeo di-serve)
- `TATETA_GEO_TOKEN=` (Token API Sanctum Anda)

> [!TIP]
> **Mekanisme Failover Otomatis**: Jika layanan mikro TatetaGeo sedang offline atau dalam pemeliharaan, sistem Aksara secara otomatis mengaktifkan **fallback dinamis ke Emsifa CDN** demi menjaga kelancaran operasional pendaftaran dan pencarian wilayah tanpa mengganggu pengguna!

---

## 🔑 Akun Akses Default
Gunakan password default: **`password`** untuk semua akun berikut:

### 1. Akun Staff & Pendidik (Static Seeder)

| Role | Username / Email | Dasbor Akses | Keterangan |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@aksara.com` / `admin` | `/admin` | Akses penuh sistem |
| **Guru Wali** | `eni@aksara.com` / `eni` | `/admin` | Wali Kelas 1 - A (Eni Nuryanti) |
| **Guru Mapel** | `beni@aksara.com` / `beni` | `/admin` | Guru PJOK (Beni Putra) |
| **Staff TU** | `sarah@aksara.com` / `sarah` | `/admin` | Bendahara (Siti Sarah) |

---

### 2. Akun Siswa & Wali (Dynamic Seeder)
Untuk menjaga keamanan data dan menyimulasikan sekolah nyata, akun **Siswa** dan **Wali** dibuat secara dinamis menggunakan domain **`@aksara.samastanuswantara.com`** dengan format sebagai berikut:

*   **Akun Siswa**:
    *   **Format Email**: `[namasiswa]_[hash]_[no]@aksara.samastanuswantara.com` (Contoh: `ahmadsaputra_7af3d2_1@aksara.samastanuswantara.com`)
    *   **Username**: `[namasiswa]_[hash]_[no]` (Contoh: `ahmadsaputra_7af3d2_1`)
    *   **Dasbor Akses**: `/dashboard` (Portal Siswa)
*   **Akun Wali / Orang Tua**:
    *   **Format Email**: `wali_[siswa_username]@aksara.samastanuswantara.com` (Contoh: `wali_ahmadsaputra_7af3d2_1@aksara.samastanuswantara.com`)
    *   **Username**: `wali_[siswa_username]` (Contoh: `wali_ahmadsaputra_7af3d2_1`)
    *   **Dasbor Akses**: `/dashboard` (Portal Wali)

> [!NOTE]
> Anda dapat melihat daftar lengkap siswa dan wali kelas yang terdaftar langsung melalui Panel Admin di menu **Siswa** atau **Wali** untuk mengambil email uji coba secara spesifik.

---

## 🚀 Menjalankan Aplikasi
Gunakan skrip pengembangan terpadu dari Composer yang akan otomatis menjalankan **Server Laravel**, **Queue Worker** (untuk notifikasi WA), dan **Vite** secara serentak dalam satu terminal:

```bash
composer dev
```

Aplikasi dapat diakses di `http://localhost:8000/admin` (Admin) atau `http://localhost:8000/dashboard` (Siswa/Wali).

---

## 👥 Authors

Proyek ini dikembangkan dengan dedikasi oleh:

-   [![septiandwica](https://img.shields.io/badge/GitHub-septiandwica-181717?style=flat&logo=github)](https://github.com/septiandwica)
-   [![itsnacla](https://img.shields.io/badge/GitHub-itsnacla-181717?style=flat&logo=github)](https://github.com/itsnacla)
-   [![nadakmlia](https://img.shields.io/badge/GitHub-nadakmlia-181717?style=flat&logo=github)](https://github.com/nadakmlia)

---

Developed for **Samasta Teknologi Nuswantara**.