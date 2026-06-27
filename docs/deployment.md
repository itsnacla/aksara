# Panduan Deployment Laravel & Filament (Aksara)

Dokumen ini menjelaskan langkah-langkah memindahkan aplikasi Aksara dari lingkungan lokal ke tiga target server: **cPanel (Shared Hosting)**, **Virtual Private Server (VPS)**, dan **Server Lokal (LAN Sekolah)**.

---

## 📋 Persiapan Sebelum Mengunggah (Lokal)

Sebelum melakukan deployment ke server mana pun, jalankan langkah-langkah kompilasi dan pembersihan cache berikut di komputer lokal Anda:

1. **Kompilasi Aset Produksi (Vite):**
   ```bash
   npm run build
   ```
   *Perintah ini akan mengompilasi semua berkas CSS & JS Filament ke folder `public/build`.*

2. **Bersihkan Cache Konfigurasi Lokal:**
   ```bash
   php artisan config:clear
   php artisan cache:clear
   ```

---

## 📁 BAGIAN 1: Deployment ke cPanel (Shared Hosting)

Metode paling aman untuk cPanel adalah meletakkan file inti Laravel di luar folder `public_html` agar file `.env` tidak dapat diakses secara publik.

### 1. Struktur Direktori di cPanel
Buat struktur folder seperti berikut di File Manager cPanel Anda:
```text
/home/username/
  ├── aksara_app/           <-- Tempatkan seluruh isi zip proyek Anda di sini (tanpa node_modules)
  │     ├── app/
  │     ├── bootstrap/
  │     ├── config/
  │     └── ...
  └── public_html/          <-- Folder bawaan cPanel. Salin isi dari folder `aksara_app/public` ke sini
```

### 2. Penyesuaian `index.php`
Edit file `/home/username/public_html/index.php` dan ubah baris pemanggilan autoload menjadi:
```php
require __DIR__.'/../aksara_app/vendor/autoload.php';
$app = require_once __DIR__.'/../aksara_app/bootstrap/app.php';
```

### 3. Database & File `.env`
1. Masuk ke cPanel -> **PostgreSQL Databases** dan buat database serta user baru.
2. Edit file `/home/username/aksara_app/.env` dan sesuaikan koneksinya:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://domain-sekolah.sch.id
   
   DB_CONNECTION=pgsql
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=username_database
   DB_USERNAME=username_user
   DB_PASSWORD=password_anda
   DB_TIMEZONE=Asia/Jakarta
   ```

### 4. Eksekusi Migrasi
Jalankan migrasi via SSH (jika aktif):
```bash
cd /home/username/aksara_app
php artisan migrate --force
php artisan db:seed --class=PermissionSeeder --force
```
*Jika tidak ada SSH, Anda bisa membuat perintah Cron Job sekali jalan untuk memicu perintah di atas.*

### 5. WebSocket (Laravel Reverb) di Shared Hosting
Karena shared hosting tidak mengizinkan port kustom dan background service berjalan terus-menerus, **Reverb lokal tidak bisa dijalankan**. 
* **Solusi:** Daftar akun di [Pusher.com](https://pusher.com) dan ubah driver pengiriman real-time Anda di `.env`:
  ```env
  BROADCAST_CONNECTION=pusher
  PUSHER_APP_ID=id-app
  PUSHER_APP_KEY=key-app
  PUSHER_APP_SECRET=secret-app
  PUSHER_APP_CLUSTER=ap1
  ```

---

## 🖥️ BAGIAN 2: Deployment ke VPS Server (Ubuntu Server + Nginx)

VPS memberikan kontrol penuh atas server, port kustom, serta memungkinkan Anda menjalankan daemon background untuk **Laravel Reverb** secara mandiri.

### 1. Instalasi Stack di VPS (Ubuntu 22.04 / 24.04 LTS)
Masuk sebagai root di VPS Anda dan jalankan instalasi:
```bash
sudo apt update && sudo apt upgrade -y
# Install Nginx & Git
sudo apt install nginx git zip unzip curl -y
# Install PHP 8.4 & Extensions
sudo apt install php8.4-fpm php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd -y
# Install PostgreSQL
sudo apt install postgresql postgresql-contrib -y
```

### 2. Konfigurasi Database PostgreSQL
Masuk ke terminal PostgreSQL dan buat database:
```bash
sudo -i -u postgres psql
CREATE DATABASE aksara;
CREATE USER aksara_user WITH PASSWORD 'password_vps_anda';
GRANT ALL PRIVILEGES ON DATABASE aksara TO aksara_user;
\q
```

### 3. Clone Proyek & Konfigurasi Berkas
Pindahkan proyek Anda ke `/var/www/`:
```bash
cd /var/www
sudo git clone https://github.com/username/aksara.git
cd aksara
sudo cp .env.example .env
# Edit .env dan masukkan kredensial DB pgsql Anda
sudo nano .env
# Install Composer Dependencies
composer install --no-dev -o
# Set Izin Direktori
sudo chown -R www-data:www-data /var/www/aksara/storage
sudo chown -R www-data:www-data /var/www/aksara/bootstrap/cache
```

### 4. Konfigurasi Nginx Server Block
Buat file konfigurasi Nginx di `/etc/nginx/sites-available/aksara`:
```nginx
server {
    listen 80;
    server_name domain-sekolah.sch.id;
    root /var/www/aksara/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # Reverse Proxy untuk Laravel Reverb (WebSockets)
    location /app {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "Upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```
Aktifkan konfigurasi dan restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/aksara /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### 5. Mengatur Supervisor untuk Reverb & Queue Worker
Agar Laravel Reverb dan Queue worker berjalan terus-menerus di latar belakang VPS:
```bash
sudo apt install supervisor -y
```
Buat file konfigurasi baru di `/etc/supervisor/conf.d/aksara.conf`:
```ini
[program:aksara-reverb]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aksara/artisan reverb:start --host=127.0.0.1 --port=8080
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/aksara/storage/logs/reverb.log

[program:aksara-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aksara/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/aksara/storage/logs/worker.log
```
Jalankan Supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start all
```

---

## 🔌 BAGIAN 3: Deployment ke Server Lokal (LAN Sekolah)

Server lokal biasanya diletakkan langsung di lingkungan sekolah menggunakan komputer server khusus yang terhubung ke jaringan lokal (LAN/Wi-Fi Sekolah).

### 1. Konfigurasi IP Statis (Static IP)
Supaya alamat server tidak berubah-ubah saat komputer server dinyalakan ulang, Anda wajib mengatur IP Statis pada sistem operasi server (misal: Linux Server atau Windows Server):
* Contoh alokasi IP lokal: **`192.168.1.200`**
* Gateway: **`192.168.1.1`** (sesuaikan dengan router sekolah Anda).

### 2. Akses Server Tanpa Internet
Komputer klien (guru/siswa) yang terhubung ke Wi-Fi sekolah bisa mengakses portal dengan cara mengetik alamat IP server pada browser:
`http://192.168.1.200`

### 3. Menggunakan Domain Lokal (Opsional & Menarik)
Agar pengguna tidak perlu mengetik IP angka, Anda bisa memetakan IP tersebut ke nama domain lokal seperti **`aksara.local`** atau **`portal.sekolah`**:
* **Metode 1 (Router DNS):** Jika sekolah menggunakan router Mikrotik, masuk ke menu **IP -> DNS -> Static** dan tambahkan data baru: `Name: aksara.local`, `Address: 192.168.1.200`.
* **Metode 2 (File Hosts):** Jika tidak memiliki akses router, edit file `hosts` di komputer masing-masing klien:
  * Windows: `C:\Windows\System32\drivers\etc\hosts`
  * Mac/Linux: `/etc/hosts`
  * Tambahkan baris: `192.168.1.200 aksara.local`

### 4. Menjalankan Background Worker di Windows Server
Jika server lokal Anda menggunakan **Windows Server / Windows 10/11 PRO**:
1. Gunakan XAMPP atau Laragon untuk menjalankan Apache/Nginx, PHP, dan PostgreSQL untuk Windows.
2. Agar Reverb (`reverb:start`) dan Worker (`queue:work`) tetap berjalan meskipun aplikasi terminal di-close, gunakan aplikasi pihak ketiga bernama **NSSM (Non-Sucking Service Manager)**:
   * Unduh NSSM, buka Command Prompt (Run as Administrator) lalu ketik:
     ```cmd
     nssm install AksaraReverb
     ```
   * Konfigurasikan GUI NSSM:
     * **Path:** `C:\path\to\php\php.exe`
     * **Startup directory:** `C:\laragon\www\aksara`
     * **Arguments:** `artisan reverb:start`
   * Klik **Install Service** dan jalankan service tersebut dari Windows Services (`services.msc`). lakukan hal yang sama untuk `artisan queue:work`.

### 5. Backup Database Otomatis (Sangat Krusial untuk Server Lokal)
Server lokal rentan terhadap mati listrik mendadak atau kerusakan hardware. Anda wajib membuat script backup otomatis.
* **Untuk Linux Server (Cron Job):**
  Buat berkas script shell `/home/admin/backup.sh`:
  ```bash
  #!/bin/bash
  BACKUP_DIR="/home/admin/backups"
  mkdir -p $BACKUP_DIR
  pg_dump -U aksara_user -h localhost aksara > $BACKUP_DIR/aksara_$(date +%F).sql
  # Hapus backup yang lebih tua dari 30 hari
  find $BACKUP_DIR -type file -mtime +30 -delete
  ```
  Masukkan ke dalam cron job (`crontab -e`) agar berjalan setiap malam pukul 00:00:
  ```text
  0 0 * * * /bin/bash /home/admin/backup.sh
  ```
* **Untuk Windows Server:**
  Gunakan **Windows Task Scheduler** untuk memicu eksekusi file batch `.bat` berisi perintah `pg_dump` ke direktori penyimpanan eksternal (seperti Flashdisk/Harddisk eksternal).
