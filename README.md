# MUA Manager

Aplikasi manajemen booking dan invoice untuk Makeup Artist (MUA) berbasis Progressive Web App (PWA). Dibangun dengan Laravel 13, Livewire, dan Tailwind CSS.

---

## Fitur Utama

- **Manajemen Booking** — Tambah, edit, dan pantau status booking klien (pending, confirmed, done, cancelled)
- **Down Payment (DP)** — Catat DP dan sisa pembayaran per booking
- **Multi-item Booking** — Satu booking bisa mencakup beberapa layanan sekaligus
- **Manajemen Klien** — Buku kontak klien lengkap beserta riwayat booking
- **Manajemen Layanan** — Kelola daftar layanan beserta harga
- **Invoice Otomatis** — Invoice bergaya elegan (PDF) dibuat otomatis saat booking dikonfirmasi
- **Kirim Invoice via WhatsApp** — Invoice PDF dikirim langsung ke nomor WhatsApp klien menggunakan WhatsApp Gateway
- **Profil Studio** — Upload logo, nama studio, catatan pembayaran yang tampil di invoice
- **Admin Panel** — Kelola semua pengguna dari panel admin
- **PWA-ready** — Bisa di-install sebagai aplikasi di ponsel

---

## Tech Stack

| Layer      | Teknologi                                 |
| ---------- | ----------------------------------------- |
| Framework  | Laravel 13                                |
| UI Reaktif | Livewire 3 + Volt                         |
| CSS        | Tailwind CSS 3                            |
| Build Tool | Vite 8                                    |
| PDF        | barryvdh/laravel-dompdf                   |
| WhatsApp   | go-whatsapp-web-multidevice (self-hosted) |
| Database   | MySQL / MariaDB                           |
| Auth       | Laravel Breeze                            |

---

## Persyaratan Sistem

- PHP >= 8.3
- Composer
- Node.js >= 18 + npm
- MySQL / MariaDB
- Ekstensi PHP: `gd`, `mbstring`, `pdo_mysql`, `fileinfo`

---

## Instalasi

### 1. Clone & Install Dependensi

```bash
git clone <repo-url> mua-app
cd mua-app
composer install
npm install
```

### 2. Konfigurasi Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:

```env
APP_NAME="MUA Manager"
APP_URL=http://mua-app.test

DB_DATABASE=mua_app
DB_USERNAME=root
DB_PASSWORD=

# WhatsApp Gateway (opsional)
WHATSAPP_GATEWAY_URL=https://your-wa-gateway-url
WHATSAPP_GATEWAY_AUTH=user:password
WHATSAPP_DEVICE_ID=your-device-id
```

### 3. Migrasi Database & Seeder

```bash
php artisan migrate --seed
```

### 4. Storage Link

```bash
php artisan storage:link
```

### 5. Build Asset & Jalankan

```bash
npm run build
php artisan serve
```

Atau gunakan **Laragon** / **Herd** sebagai server lokal.

---

## Konfigurasi WhatsApp Gateway

Aplikasi ini menggunakan [go-whatsapp-web-multidevice](https://github.com/aldinokemal/go-whatsapp-web-multidevice) untuk mengirim invoice PDF ke klien via WhatsApp.

Saat booking dikonfirmasi dan invoice dibuat, sistem akan:

1. Generate PDF invoice secara lokal menggunakan DomPDF
2. Mengirim file PDF tersebut langsung ke nomor WhatsApp klien via endpoint `/send/file`

| Variabel                | Keterangan                              |
| ----------------------- | --------------------------------------- |
| `WHATSAPP_GATEWAY_URL`  | URL server gateway WA                   |
| `WHATSAPP_GATEWAY_AUTH` | Kredensial `user:password` (Basic Auth) |
| `WHATSAPP_DEVICE_ID`    | Device ID yang terdaftar di gateway     |

> Jika variabel ini tidak diset, fitur WA dinonaktifkan secara otomatis.

---

## Struktur Direktori Penting

```
app/
├── Http/Controllers/InvoiceController.php   # Preview & download PDF
├── Livewire/
│   ├── Bookings/                            # Form, list, detail booking
│   ├── Clients/                             # Manajemen klien
│   ├── Invoices/                            # List invoice & update status
│   └── Dashboard.php                        # Ringkasan statistik
├── Models/                                  # Booking, Client, Invoice, Service, User
└── Services/WhatsAppService.php             # Kirim invoice PDF via WA gateway

resources/views/invoices/pdf.blade.php       # Template invoice (Playfair + Montserrat)
database/migrations/                         # Skema lengkap semua tabel
```

---

## Akun Default (Seeder)

| Email               | Password   | Role  |
| ------------------- | ---------- | ----- |
| `admin@example.com` | `password` | admin |
| `mua@example.com`   | `password` | user  |

---

## Perintah Berguna

```bash
php artisan optimize:clear   # Bersihkan semua cache
php artisan view:clear       # Bersihkan cache view
php artisan migrate:fresh --seed  # Reset database + isi ulang data dummy
```

---

## Auto Deploy via GitHub Webhook

Project ini sudah dilengkapi endpoint webhook yang akan menjalankan `deploy.sh` secara otomatis.

Domain project: `https://mua.surakana.my.id`

### 1. Siapkan Environment Produksi

Tambahkan di file `.env` server:

```env
DEPLOY_BRANCH=main
GITHUB_WEBHOOK_SECRET=isi-secret-yang-sama-dengan-github
DEPLOY_HOOK_INTERNAL_TOKEN=isi-token-internal-acak-panjang
```

### 2. Endpoint Webhook

- URL webhook project: `https://mua.surakana.my.id/webhooks/github/deploy`
- Method: `POST`
- Content type: `application/json`
- Secret: gunakan nilai yang sama dengan `GITHUB_WEBHOOK_SECRET`
- Trigger event: **Just the push event**

### 2a. Jika Sudah Punya Hook Gateway (hook.surakana.my.id)

Kamu bisa tetap memakai URL webhook lama di GitHub: `https://hook.surakana.my.id/`.

Jika service kamu memakai format `hook.json` (seperti project sheza), contoh untuk project ini:

```json
[
	{
		"id": "deploy-mua",
		"execute-command": "/DATA/AppData/mua-app/deploy.sh",
		"command-working-directory": "/DATA/AppData/mua-app"
	}
]
```

Maka endpoint yang dipanggil GitHub biasanya menjadi:

- `https://hook.surakana.my.id/hooks/deploy-mua`

Lalu dari service hook gateway, forward payload ke endpoint project dengan header token internal:

```bash
curl -X POST "https://mua.surakana.my.id/webhooks/github/deploy" \
	-H "Content-Type: application/json" \
	-H "X-GitHub-Event: push" \
	-H "X-Hub-Signature-256: sha256=..." \
	-H "X-Deploy-Token: isi-token-internal-acak-panjang" \
	--data-binary @payload.json
```

Catatan:
- `X-Hub-Signature-256` sebaiknya tetap diteruskan agar verifikasi signature GitHub tetap aktif.
- `X-Deploy-Token` wajib sama dengan `DEPLOY_HOOK_INTERNAL_TOKEN` pada server project.

### 3. Contoh Konfigurasi Nginx (Laravel + PHP-FPM)

```nginx
server {
	listen 80;
	server_name domain-anda.com;
	root /DATA/AppData/mua-app/public;

	index index.php;

	location / {
		try_files $uri $uri/ /index.php?$query_string;
	}

	location ~ \.php$ {
		include fastcgi_params;
		fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
		fastcgi_pass unix:/run/php/php8.3-fpm.sock;
	}
}
```

### 4. Permission yang Dibutuhkan

```bash
chmod +x deploy.sh
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Log Deploy

- Log proses webhook launcher: `storage/logs/deploy-hook.log`
- Log proses deploy script: `storage/logs/deploy.log`

Jika signature valid dan push masuk ke branch target, deploy akan berjalan otomatis.

---

## Lisensi

MIT License
