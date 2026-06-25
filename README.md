# AKSARA

Aksara adalah sistem manajemen sekolah yang dirancang untuk menjadi pusat data pendidikan yang dinamis, akurat, dan transparan. Proyek ini menggabungkan kekuatan **Filament PHP** untuk manajemen data tingkat tinggi dengan **Portal Kustom** yang intuitif bagi siswa dan orang tua.

---

## 🚀 Fitur Utama

-   **QR Attendance & WA Gateway (Fonnte)**: Sistem absensi berbasis QR Code (*Kiosk Standalone*) yang otomatis me-*trigger* *Background Jobs* untuk mengirimkan notifikasi WhatsApp *realtime* ke orang tua tanpa membebani *server* utama.
-   **AI-Powered Ecosystem (PG Vector RAG)**: Aksara mengemas 3 lapis asisten cerdas: `AksaraAssistant` (Chatbot Portal Edukasi), `WaliKelasAgent` (Pembuat narasi Rapor otomatis), dan `DataScientistAssistant` (Analitik performa sekolah).
-   **Portal Live Dashboard (WebSockets)**: Ekosistem antarmuka (*frontend*) mandiri bagi Siswa dan Wali Murid. Menampilkan pembaruan jadwal akademik, absensi, dan nilai secara seketika (*realtime*) memanfaatkan **Laravel Reverb & Echo**.
-   **Evaluasi Akademik & Kurikulum Merdeka (P5)**: Mendukung arsitektur Kurikulum Merdeka (Tema & Proyek P5) serta Ekstrakurikuler, dilengkapi fitur **Grade Monitoring** sentral yang menggunakan mekanisme penguncian (*Status Lock*).
-   **Manajemen Buku Induk Otomatis**: Integrasi `BukuIndukService` yang langsung menghimpun *ledger* (buku besar) nilai 6 semester siswa secara otomatis, dipadukan dengan modul cetak **E-Rapor PDF** pintar.
-   **Hybrid Authentication & Impersonation**: Sistem pembagian peran (RBAC Filament Shield) adaptif dengan kemampuan **Login As** (`ImpersonateController`), mempermudah *Super Admin* menguji *platform* dari kacamata *user* manapun.
-   **Antarmuka Premium Cepat**: Dibangun di atas fondasi Filament PHP ~5.0 untuk panel *Backoffice* yang elegan dan Tailwind CSS 4.0 pada *Frontend* Portal.

---

## 🌟 Pembaruan & Peningkatan Sistem Terkini

-   **Live Grade Progress Tracker**: Kurikulum dan Kepala Sekolah kini bisa melacak (*track*) secara instan guru mana yang belum menyelesaikan pengisian nilai, dipermudah lewat visualisasi *Gauge UI* di *dashboard* admin.
-   **Integrasi Wilayah Cerdas (TatetaGeo & Kemendikbud)**: Pencarian referensi wilayah & sekolah distandarisasi lewat panggilan *service* terpusat menggunakan mikroservis **TatetaGeo** dan *fallback* mulus ke Emsifa CDN.
-   **Otomatisasi Kenaikan Kelas & Alumni**: Kehadiran `PromotionService` merampingkan proses rotasi tahun ajaran baru, pendaftaran kelas masal, hingga pengarsipan rekam jejak alumni di modul *Graduate Profile*.
-   **Cetak Kartu Pelajar (QR Generation)**: Admin maupun Tata Usaha dapat dengan mudah mengekspor kartu ID ber-QR unik siap cetak secara *batch* untuk ribuan siswa (`StudentCardController`).
-   **Pusat Kendali Penugasan Mengajar Dinamis**: Resolusi bentrok jadwal guru otomatis tertangani di balik layar oleh *Schedule Generator Service*, yang mencocokkan jam mengajar, ruang kelas, dan mata pelajaran tanpa benturan (*clash*).
-   **Manajemen Cuti & Izin Terpadu**: Pengajuan izin sakit/absen siswa (beserta surat dokter) dapat dikirim *online* melalui portal siswa, dan langsung ditindaklanjuti/divalidasi oleh guru bersangkutan (*Student Leaves*).

---

## 🛠️ Tech Stack

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white)
![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![Vite](https://img.shields.io/badge/Vite-646CFF?style=for-the-badge&logo=vite&logoColor=white)
![Filament](https://img.shields.io/badge/Filament-FACC15?style=for-the-badge&logo=laravel&logoColor=black)

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

## 📊 Arsitektur Sistem (Mermaid Diagram)

```mermaid
graph TD
    %% End Users & Interfaces
    Siswa[Siswa & Orang Tua] -->|Akses Portal Live| Portal[Portal Frontend<br>Blade + Livewire + Tailwind]
    Guru[Guru & Admin] -->|Akses Backoffice| AdminPanel[Filament Admin Panel]
    
    %% Core System
    Portal --> Core[Laravel 13 Core]
    AdminPanel --> Core
    
    %% Background Tasks & Realtime
    Core -->|Event Broadcast| Reverb[Laravel Reverb / Echo]
    Reverb -.->|WebSocket Updates| Portal
    Core -->|Queued Jobs| Queue[Background Queue]
    
    %% External Gateways
    Queue -->|Push Notification| WA[WhatsApp Gateway<br>Fonnte]
    Core -->|Sync Wilayah| TatetaGeo[TatetaGeo Microservice]
    TatetaGeo -.->|Failover| Emsifa[Emsifa CDN]
    
    %% Database & AI
    Core --> DB[(PostgreSQL 16+)]
    Core --> AIAgent[AI Assistants<br>Aksara, WaliKelas, DataScientist]
    AIAgent --> RAG[AksaraKnowledgeBase]
    RAG --> PGVector[(PG Vector)]
    DB --- PGVector
```

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