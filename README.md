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

## Lisensi

MIT License
