# Keputusan Proyek

Dokumen ini mencatat semua keputusan yang dibuat selama proyek berjalan. Append-only: entri lama tidak dihapus atau ditulis ulang.

## [2026-10-14 21:00 WIB] Sesi 0 - Keputusan Foundasi

### 1. Penamaan Kolom Database
**Keputusan:** Gunakan konvensi ERD dengan `id_*`
- Primary key: `id_admin`, `id_room`, `id_booking`, dll
- Foreign key: `id_*` sesuai ERD v3
- **Alasan:** Konsistensi dengan ERD, menghindari confusion saat mapping kontrak BE-FE

### 2. Nama Folder Proyek
**Keputusan:** Gunakan `project-clicked-booking`
- Folder existing dipertahankan
- Git repository akan diinit di folder ini

### 3. Penyimpanan Gambar QRIS & Template WhatsApp
**Keputusan:** Tabel `settings` baru (perubahan ERD)
- Admin dapat mengelola via UI di halaman Pengaturan
- Tidak perlu redeploy untuk ubah gambar QRIS atau template pesan
- **Action:** Buat ERD v4 dengan tabel `settings`, jangan ubah ERD v3

### 4. Isian Pembayaran di Step 3 (Booking)
**Keputusan:** Hanya unggah bukti bayar
- Sistem ambil nominal dari `bookings.total_harga`
- Tanggal bayar = server `now()`
- Pelanggan tidak perlu input manual nominal atau tanggal

### 5. Pembayaran Penuh vs DP
**Keputusan:** Pembayaran penuh (full amount)
- Tanpa fitur DP untuk kesederhanaan MVP
- Nominal di bukti harus sesuai `total_harga`

### 6. Notifikasi Pembayaran via WhatsApp
**Keputusan:** Sistem kirim notifikasi pembayaran booking via WhatsApp dengan 3 pesan:
1. **Struk payment pending** — saat pelanggan upload bukti (booking status = pending)
2. **Notifikasi tolak** — jika admin tolak bukti bayar
3. **Struk konfirmasi** — jika admin terima bukti (booking status = confirmed)

**Implikasi:**
- Butuh 3 WhatsApp jobs untuk booking (selain 1 job untuk edit foto)
- Butuh template pesan untuk 3 jenis notifikasi ini
- Pelanggan dapat cek status secara aktif di `/booking/{kode}` atau pasif via WhatsApp

**Action untuk implementasi:**
- Saat bukti ditolak: pelanggan bisa upload ulang bukti tanpa membuat booking baru
- Status payment tetap `pending` sampai bukti diterima

### 7. Edit Foto Berbayar atau Gratis
**Keputusan:** Edit foto GRATIS
- Layanan edit foto tidak memerlukan pembayaran terpisah
- Tabel `payments` hanya untuk booking
- Kolom `id_request` di `payments` tetap ada (nullable, unused untuk MVP)

### 8. Cara Pelanggan Memilih Foto yang Diedit
**Keputusan:** Foto dipilih oleh admin (Opsi C - sementara)
- Form edit foto: pelanggan hanya isi instruksi edit umum
- Admin yang menentukan foto mana yang diedit saat memproses request
- **Status:** Pending diskusi dengan client, bisa berubah nanti

### 9. Akses Form Edit Foto dari Luar Studio
**Keputusan:** Boleh dari mana saja (tidak ada pembatasan lokasi/IP)
- Siapa saja bisa akses URL `/edit-foto/{id_room}` dari mana saja
- Tidak ada validasi IP atau geolocation studio

### 10. Provider Captcha
**Keputusan:** Defer untuk nanti
- Captcha tidak diimplementasikan di MVP awal
- Akan ditambahkan kemudian jika diperlukan

### 11. Provider WhatsApp
**Keputusan:** Defer detail implementasi
- Provider WhatsApp akan ditentukan kemudian
- Interface `WhatsAppGateway` akan dibuat untuk memudahkan ganti provider
- **Status:** Menunggu clarification untuk opsi implementasi

### 12. Autentikasi Google Drive
**Keputusan:** Service Account (Google Cloud)
- Headless authentication dengan credential JSON
- Cocok untuk app automation
- Satu service account untuk semua operasi

### 13. Hosting
**Keputusan:** Defer untuk nanti
- Code akan dibuat dulu, hosting ditentukan saat deployment
- Queue worker setup akan disesuaikan dengan hosting yang dipilih

### 14. Hak Akses Link Folder Drive
**Keputusan:** Public link (siapa saja dengan link)
- Pelanggan bisa buka link tanpa login Google
- Link hanya dikirim via WhatsApp ke pelanggan
- Kemudahan akses > security untuk MVP

### 15. Sumber Hitungan Jumlah Pelanggan (Dashboard)
**Keputusan:** Dari booking saja
- Count unique `bookings.no_hp_pelanggan`
- Tidak termasuk nomor WA dari `photo_edit_requests`

### 16. Domain Final
**Keputusan:** Defer untuk nanti
- Domain akan ditentukan saat deployment
- QR code akan di-generate dengan domain final saat siap

### 17. Retensi Foto di Google Drive
**Keputusan:** Defer untuk nanti
- Belum ada auto-delete, foto disimpan permanen sampai admin hapus manual
- Fitur retention/auto-delete di luar scope MVP

---

## Keputusan Pending / Deferred

Berikut keputusan yang di-defer untuk implementasi nanti:
1. Provider WhatsApp & cara pengiriman pesan (API vs link pre-filled)
2. Provider Captcha (untuk form edit foto)
3. Hosting environment & queue worker setup
4. Domain final untuk QR code
5. Retention policy foto di Google Drive
6. Cara admin diberi tahu ada request baru (notifikasi)
7. Cara pelanggan memilih foto yang diedit (pending diskusi dengan client)

Keputusan ini akan dibahas dan dicatat saat diperlukan untuk implementasi.