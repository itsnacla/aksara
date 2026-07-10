# Panduan Deployment Laravel & Filament (Aksara)

Dokumen ini menjelaskan langkah-langkah memindahkan aplikasi Aksara dari lingkungan lokal ke tiga target server: **cPanel (Shared Hosting)**, **Virtual Private Server (VPS)**, dan **Server Lokal (LAN Sekolah)**, baik menggunakan metode manual maupun berbasis **Git**.

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

### Opsi A: Deployment Manual (Menggunakan ZIP)

1. **Struktur Direktori di cPanel:**
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

2. **Penyesuaian `index.php`:**
   Edit file `/home/username/public_html/index.php` dan ubah baris pemanggilan autoload menjadi:
   ```php
   require __DIR__.'/../aksara_app/vendor/autoload.php';
   $app = require_once __DIR__.'/../aksara_app/bootstrap/app.php';
   ```

3. **Database & File `.env`:**
   * Masuk ke cPanel -> **PostgreSQL Databases** dan buat database serta user baru.
   * Edit file `/home/username/aksara_app/.env` dan sesuaikan koneksinya.

---

### Opsi B: Deployment Otomatis (Menggunakan Git Version Control cPanel)

cPanel menyediakan fitur Git bawaan yang mempermudah deployment dan pembaruan kode tanpa perlu mengunggah ulang ZIP secara manual.

1. **Buat Repositori Git di cPanel:**
   * Masuk ke dashboard cPanel -> cari menu **Git Version Control**.
   * Klik **Create**.
   * Masukkan **Clone URL** repositori GitHub/GitLab Anda (misal: `https://github.com/username/aksara.git`).
   * Isi **Repository Path** ke `/home/username/aksara_app`.
   * Isi **Display Name** (misal: `Aksara`).
   * Klik **Create**.

2. **Gunakan Berkas Otomatisasi `.cpanel.yml`:**
   cPanel akan menjalankan perintah penyalinan berkas secara otomatis setiap kali Anda melakukan push ke repositori GitHub. Buat file bernama `.cpanel.yml` di direktori utama proyek lokal Anda, lalu komit dan push ke GitHub:
   ```yaml
   ---
   deployment:
     tasks:
       - export DEPLOYPATH=/home/username/aksara_app
       # Sinkronisasikan berkas kode ke folder aksara_app (kecuali node_modules & berkas tersembunyi)
       - /bin/rsync -av --exclude="node_modules" --exclude=".git" --exclude="public" * $DEPLOYPATH
       # Pindahkan aset publik (index.php, build css/js, dll.) ke public_html
       - /bin/rsync -av public/ /home/username/public_html/
   ```

3. **Jalankan Instalasi Dependensi (SSH / Terminal cPanel):**
   Setelah melakukan pull/update kode di cPanel:
   ```bash
   cd /home/username/aksara_app
   composer install --no-dev -o
   php artisan migrate --force
   ```

4. **Menjalankan Queue Worker (Background Jobs WhatsApp) di cPanel:**
   Karena cPanel biasanya tidak mengizinkan daemon berjalan selamanya (Supervisor), kita harus menggunakan **Cron Jobs** agar notifikasi WhatsApp tetap terkirim:
   * Masuk ke menu **Cron Jobs** di cPanel.
   * Tambahkan Cron baru dengan jadwal **Once Per Minute (* * * * *)**.
   * Masukkan perintah berikut (sesuaikan versi PHP dan username Anda):
     ```bash
     /usr/local/bin/php /home/username/aksara_app/artisan queue:work --stop-when-empty > /dev/null 2>&1
     ```

---

## 🖥️ BAGIAN 2: Deployment ke VPS Server (Ubuntu Server + Nginx)

VPS memberikan kontrol penuh atas server, port kustom, serta memungkinkan Anda menjalankan daemon background untuk **Laravel Reverb** secara mandiri.

### 1. Instalasi Stack di VPS (Ubuntu 22.04 / 24.04 LTS)
Masuk sebagai root di VPS Anda dan jalankan instalasi:
```bash
sudo apt update && sudo apt upgrade -y
sudo apt install nginx git zip unzip curl -y
sudo apt install php8.4-fpm php8.4-cli php8.4-pgsql php8.4-mbstring php8.4-xml php8.4-curl php8.4-zip php8.4-gd -y
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

---

### Opsi A: Deployment Pertama kali via Git clone
1. Clone repositori ke direktori `/var/www/`:
   ```bash
   cd /var/www
   sudo git clone https://github.com/username/aksara.git
   cd aksara
   ```
2. Buat file `.env` dan pasang dependensi:
   ```bash
   sudo cp .env.example .env
   sudo nano .env # Kredensial DB PostgreSQL dimasukkan di sini
   composer install --no-dev -o
   ```
3. Atur kepemilikan direktori:
   ```bash
   sudo chown -R www-data:www-data /var/www/aksara/storage
   sudo chown -R www-data:www-data /var/www/aksara/bootstrap/cache
   ```

---

### Opsi B: Skrip Pembaruan Otomatis dengan Git (VPS & Local Server)
Agar proses update kode, migrasi, dan pembetulan cache di VPS bisa diselesaikan dengan satu perintah, buat file skrip deploy bernama `deploy.sh` di folder `/var/www/aksara/`:
```bash
#!/bin/bash
set -e

echo "=== Memulai Pembaruan Sistem (Git) ==="
# Masuk ke mode maintenance mode
php artisan down --message="Sistem sedang diperbarui oleh administrator."

# Tarik kode terbaru
git pull origin main

# Install composer dependencies
composer install --no-dev -o

# Migrasi Database
php artisan migrate --force

# Install Node & Build Assets (opsional jika dikompilasi di server)
# npm install && npm run build

# Clear and Cache Configurations
php artisan optimize
php artisan view:cache

# Hidupkan kembali aplikasi
php artisan up

echo "=== Pembaruan Sukses Selesai ==="
```
Jadikan berkas ini dapat dieksekusi:
```bash
chmod +x deploy.sh
```
Setiap kali Anda ingin memperbarui aplikasi, Anda tinggal masuk ke SSH dan menjalankan:
```bash
./deploy.sh
```

---

### 3. Konfigurasi Nginx Server Block
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

### 4. Konfigurasi Supervisor (Untuk Queue Worker & Reverb)
VPS memungkinkan kita menjalankan proses *background* secara permanen. Instal Supervisor untuk menjaga agar pengirim pesan WhatsApp (`queue:work`) dan WebSocket (`reverb`) terus menyala:
```bash
sudo apt install supervisor -y
```
Buat file konfigurasi worker `/etc/supervisor/conf.d/aksara-worker.conf`:
```ini
[program:aksara-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/aksara/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
stdout_logfile=/var/www/aksara/storage/logs/worker.log
```
Aktifkan supervisor:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start aksara-worker:*
```

---

## 🔌 BAGIAN 3: Deployment ke Server Lokal (LAN Sekolah)

Server lokal biasanya diletakkan langsung di lingkungan sekolah menggunakan komputer server khusus yang terhubung ke jaringan lokal (LAN/Wi-Fi Sekolah).

### 1. Pembaruan Kode via Git di Server Lokal
Jika Anda memiliki server lokal berbasis Linux, Anda dapat menerapkan **Opsi B (Skrip Pembaruan deploy.sh)** seperti di VPS. Anda cukup menjalankan `./deploy.sh` untuk melakukan pull dari repositori git lokal sekolah atau GitHub.

### 2. Konfigurasi IP Statis (Static IP)
Supaya alamat server tidak berubah-ubah saat komputer server dinyalakan ulang, Anda wajib mengatur IP Statis pada sistem operasi server (misal: Linux Server atau Windows Server):
* Contoh alokasi IP lokal: **`192.168.1.200`**
* Gateway: **`192.168.1.1`** (sesuaikan dengan router sekolah Anda).

### 3. Akses Server Tanpa Internet
Komputer klien (guru/siswa) yang terhubung ke Wi-Fi sekolah bisa mengakses portal dengan cara mengetik alamat IP server pada browser:
`http://192.168.1.200`

### 4. Menggunakan Domain Lokal (Opsional)
Agar pengguna tidak perlu mengetik IP angka, Anda bisa memetakan IP tersebut ke nama domain lokal seperti **`aksara.local`** atau **`portal.sekolah`**:
* **Metode 1 (Router DNS Mikrotik):** Masuk ke Mikrotik -> menu **IP -> DNS -> Static** dan tambahkan data baru: `Name: aksara.local`, `Address: 192.168.1.200`.
* **Metode 2 (File Hosts Klien):** Tambahkan baris `192.168.1.200 aksara.local` ke dalam file `hosts` di komputer masing-masing guru.
