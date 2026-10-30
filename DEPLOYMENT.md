# Panduan Instalasi dan Deployment

Dokumen ini menjelaskan cara memasang **Sistem Manajemen Laundry** untuk pengembangan lokal dan menerapkannya ke server produksi.

> Ringkasan file environment:
> - `.env.development.example` — template untuk pengembangan lokal (SQLite, debug aktif).
> - `.env.example` — template untuk produksi (MySQL, debug mati, HTTPS).
> - `.env` — konfigurasi aktual yang dibuat dari salah satu template di atas. File ini **tidak boleh** di-commit.

---

## 1. Kebutuhan Sistem

| Komponen | Versi minimal | Keterangan |
| --- | --- | --- |
| PHP | 8.2 | Dengan ekstensi `ctype`, `curl`, `dom`, `fileinfo`, `filter`, `gd`, `hash`, `mbstring`, `openssl`, `pcre`, `pdo`, `pdo_mysql`, `session`, `tokenizer`, `xml`, `zip` |
| Composer | 2.x | Untuk dependensi PHP |
| Node.js | 18+ dan npm | Untuk build aset (Vite + Tailwind) |
| Database | MySQL 8+ / MariaDB 10.4+ | Disarankan karena migration memakai tipe `ENUM` |
| Web server | Nginx / Apache | Document root harus diarahkan ke folder `public/` |

> SQLite didukung untuk pengembangan atau instalasi sangat kecil, tetapi kurang cocok untuk produksi multi-user karena keterbatasan konkurensi dan proses backup. Gunakan MySQL/MariaDB untuk data transaksi nyata.

---

## 2. Instalasi Pengembangan Lokal

```bash
# 1. Salin template pengembangan
cp .env.development.example .env        # Linux/macOS
copy .env.development.example .env      # Windows

# 2. Install dependensi
composer install
npm install

# 3. Generate application key
php artisan key:generate

# 4. Siapkan database SQLite
touch database/database.sqlite          # Windows: type nul > database\database.sqlite

# 5. Migrasi dan seed data awal
php artisan migrate --seed

# 6. Build aset (atau jalankan npm run dev di terminal terpisah)
npm run build

# 7. Jalankan server
php artisan serve
```

Buka `http://localhost:8000`. Akun awal hasil seeder: `admin`, `kasir`, dan `operator`, semuanya dengan password `password`.

---

## 3. Deployment ke Produksi

### 3.1 Siapkan kode dan dependensi

```bash
# Di server, dari direktori aplikasi
cp .env.example .env

composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

### 3.2 Konfigurasi environment

Edit `.env` dan sesuaikan minimal nilai berikut:

```dotenv
APP_NAME="Nama Laundry Anda"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_URL=https://laundry.domainanda.com

APP_LOCALE=id
APP_FALLBACK_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laundry_management
DB_USERNAME=laundry_user
DB_PASSWORD=password-kuat

SESSION_SECURE_COOKIE=true
LOG_STACK=daily
LOG_LEVEL=warning
```

Generate application key **satu kali**:

```bash
php artisan key:generate --force
```

> Simpan nilai `APP_KEY` di tempat aman. Mengganti `APP_KEY` akan membuat data terenkripsi dan session lama tidak dapat dibaca.

### 3.3 Siapkan database

Migration aplikasi menggunakan tipe `ENUM`, sehingga MySQL/MariaDB lebih disarankan daripada SQLite.

```sql
CREATE DATABASE laundry_management
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

CREATE USER 'laundry_user'@'localhost' IDENTIFIED BY 'password-kuat';
GRANT ALL PRIVILEGES ON laundry_management.* TO 'laundry_user'@'localhost';
FLUSH PRIVILEGES;
```

Jalankan migration dan seeder:

```bash
php artisan migrate --force
php artisan db:seed --force
```

> `--force` diperlukan karena `APP_ENV=production`.

### 3.4 Siapkan storage dan permission

```bash
php artisan storage:link

# Linux/macOS
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

Pastikan web server dapat menulis ke `storage/` dan `bootstrap/cache/`.

### 3.5 Optimasi untuk produksi

```bash
php artisan optimize
```

Perintah ini menjalankan `config:cache`, `route:cache`, dan `view:cache`. Setiap kali `.env` atau route berubah, jalankan ulang:

```bash
php artisan optimize:clear
php artisan optimize
```

> Jangan memakai `config:cache` di lingkungan lokal karena dapat mengabaikan konfigurasi `phpunit.xml` dan membuat test gagal dengan error 419.

### 3.6 Queue worker

`QUEUE_CONNECTION=database` membutuhkan worker agar job diproses. Jalankan dengan Supervisor (Linux) atau Task Scheduler (Windows):

```bash
php artisan queue:work --tries=1 --timeout=120
```

Contoh konfigurasi Supervisor:

```ini
[program:laundry-queue]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/laundry/artisan queue:work --tries=1 --timeout=120
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/laundry/storage/logs/queue.log
```

Jika aplikasi tidak memakai queue, ubah `QUEUE_CONNECTION=sync` agar job langsung dijalankan.

### 3.7 Web server

Arahkan document root ke folder `public/`, bukan ke root proyek.

Contoh Nginx:

```nginx
server {
    listen 443 ssl;
    server_name laundry.domainanda.com;
    root /var/www/laundry/public;

    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include fastcgi_params;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 3.8 HTTPS

Gunakan sertifikat TLS (misalnya Let's Encrypt). Setelah HTTPS aktif:

- `APP_URL` harus memakai `https://`.
- `SESSION_SECURE_COOKIE=true` agar cookie hanya dikirim melalui HTTPS.

Jika situs masih diuji tanpa HTTPS, set `SESSION_SECURE_COOKIE=false` sementara. Jangan biarkan kondisi ini di produksi.

---

## 4. Checklist Keamanan Setelah Deploy

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_KEY` sudah di-generate dan disimpan dengan aman.
- [ ] `.env` tidak dapat diakses publik dan tidak masuk repository.
- [ ] Database memakai user khusus aplikasi, bukan `root`, dengan password kuat.
- [ ] Password akun bawaan (`admin`, `kasir`, `operator` = `password`) sudah diganti melalui menu **Pengguna** atau dinonaktifkan.
- [ ] Situs berjalan di HTTPS dan `SESSION_SECURE_COOKIE=true`.
- [ ] Permission `storage/` dan `bootstrap/cache/` benar.
- [ ] `php artisan optimize` sudah dijalankan.
- [ ] Queue worker berjalan (jika `QUEUE_CONNECTION=database`).
- [ ] Backup database dan folder `storage/app` terjadwal.
- [ ] Log rotation aktif (`LOG_STACK=daily`).
- [ ] Directory listing dimatikan di web server.

---

## 5. Troubleshooting

| Gejala | Penyebab umum | Solusi |
| --- | --- | --- |
| Halaman 500 setelah deploy | `APP_KEY` kosong atau permission storage salah | Jalankan `php artisan key:generate --force`, perbaiki permission |
| Error 419 saat submit form | Cookie session tidak terkirim | Periksa `APP_URL`, `SESSION_DOMAIN`, dan `SESSION_SECURE_COOKIE` |
| Aset CSS/JS tidak muncul | Build aset belum dijalankan atau `APP_URL` salah | Jalankan `npm run build`, cek `APP_URL`, lalu `php artisan optimize:clear` |
| Perubahan `.env` tidak terbaca | Konfigurasi masih di-cache | Jalankan `php artisan optimize:clear` lalu `php artisan optimize` |
| Login berhasil tetapi langsung logout | `SESSION_SECURE_COOKIE=true` diakses lewat HTTP | Aktifkan HTTPS atau set `false` sementara |
| Migration gagal karena `ENUM` | Database bukan MySQL/MariaDB | Gunakan MySQL 8+/MariaDB 10.4+, atau sesuaikan migration untuk database lain |
| Job tidak diproses | Queue worker tidak berjalan | Jalankan `php artisan queue:work` via Supervisor |
| Error koneksi database | Kredensial/host salah atau ekstensi `pdo_mysql` belum aktif | Cek `DB_*` dan aktifkan `pdo_mysql` |

---

## 6. Ringkasan Perbedaan Environment

| Variabel | Pengembangan | Produksi |
| --- | --- | --- |
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost` | `https://domain-anda` |
| `APP_TIMEZONE` | `Asia/Jakarta` | `Asia/Jakarta` |
| `DB_CONNECTION` | `sqlite` | `mysql` |
| `LOG_LEVEL` | `debug` | `warning` |
| `SESSION_ENCRYPT` | `false` | `true` |
| `SESSION_SECURE_COOKIE` | `false` | `true` |
| `MAIL_MAILER` | `log` | `smtp` |
