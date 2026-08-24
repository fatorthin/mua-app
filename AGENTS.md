# AI Assistant & Developer Guidelines (AGENTS.md)

## Perhatian Penting Sebelum Mengubah / Menambah Fitur
Sebelum melakukan penambahan atau modifikasi fitur di project **MUA Manager**, Anda **WAJIB membaca dan merujuk ke dokumen berikut**:
- [SYSTEM_MAPPING.md](file:///e:/laragon/www/mua-app/SYSTEM_MAPPING.md) — Peta arsitektur lengkap, alur modul, relasi database (ERD), dan matriks file yang harus diubah per kasus.
- [prd.md](file:///e:/laragon/www/mua-app/prd.md) — Dokumen kebutuhan produk dan spesifikasi bisnis.

---

## Ringkasan Prinsip Utama
1. **Multi-Tenancy**: Data Booking, Klien, Layanan, dan Invoice wajib selalu terisolasi berdasarkan `user_id = auth()->id()`.
2. **Asynchronous Processing**: Semua pengiriman invoice atau reminder via WhatsApp Gateway wajib menggunakan Queue Job (`app/Jobs/`).
3. **Dual Render Invoice**: Setiap modifikasi desain invoice di `resources/views/invoices/pdf.blade.php` harus diimbangi di `app/Services/InvoiceRenderer.php` (metode `renderWithGd`).
4. **Format Nomor Telepon**: Selalu gunakan normalisasi `628xxx` untuk kompatibilitas gateway WhatsApp.
5. **Testing**: Pastikan regression test di `tests/Feature/Livewire/` tetap lulus setelah melakukan perubahan.
