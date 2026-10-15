# Sesi 0: Analisis Project & Persiapan

**Tanggal:** 2026-10-14 21:00 WIB  
**Status:** Selesai - Menunggu persetujuan untuk lanjut ke BE-1

---

## Ringkasan

Sesi 0 adalah sesi persiapan tanpa kode. Kami telah membaca semua dokumen PRD (Backend & Frontend), ERD v3, menganalisis kondisi project Laravel yang ada, mengajukan pertanyaan foundasi, dan mendokumentasikan keputusan.

---

## 1. Pemahaman Produk

### Gambaran Umum
Website untuk **satu studio foto UMKM** dengan **dua layanan utama**:

1. **Booking Studio**
   - Wizard 4 langkah: Data diri → Waktu → Pembayaran → Kode booking
   - Pembayaran QRIS statis + upload bukti (tanpa payment gateway)
   - Slot dikunci saat step 2 selesai
   - Admin verifikasi bukti bayar

2. **Edit Foto**
   - QR per studio → `/edit-foto/{id_room}`
   - Pelanggan isi form: nama, no. WA, instruksi edit
   - Admin verifikasi → edit → upload ke Google Drive → kirim via WhatsApp

### Aturan Bisnis Inti
- **Pelanggan TIDAK login** — akses tanpa autentikasi
- **Hanya admin yang login** (guard khusus → model `Admin`)
- **Admin TIDAK bisa booking** — hanya pelanggan
- Satu booking boleh punya banyak request edit foto
- Revisi: admin buka kembali request `dikirim` → status `diproses`

---

## 2. Kondisi Project Saat Ini

### Status Instalasi: Fresh Laravel 12
Project `project-clicked-booking/` adalah **fresh install** — tidak ada kode bisnis:

| Area | Kondisi |
|------|---------|
| **PHP & Framework** | Laravel 12, PHP 8.2 ✅ |
| **Testing** | PHPUnit (belum Pest) |
| **Frontend** | Tailwind CSS v4 ✅, Vite 7 ✅ |
| **Database** | Default: sqlite (perlu ubah ke MySQL) |
| **Migrasi** | Hanya 3 default (users, cache, jobs) |
| **Models** | Hanya `User.php` |
| **Controllers** | Kosong |
| **Routes** | Hanya `/` → welcome |
| **Views** | Hanya `welcome.blade.php` |
| **Locale** | `en` (perlu ubah ke `id` / Indonesia) |
| **Timezone** | UTC (perlu ubah ke `Asia/Jakarta`) |

**Kesimpulan:** Semua pekerjaan BE & FE masih di titik nol ✅

---

## 3. Skema Database

### ERD v4 (14 Tabel)
**13 tabel bisnis + 1 tabel settings baru:**

#### Tabel Master
1. `admins` — akun admin (guard khusus)
2. `rooms` — studio/ruangan foto
3. `room_images` — galeri foto studio
4. `room_packages` — paket booking per studio
5. **`settings` (BARU)** — pengaturan sistem (QRIS, template WA)

#### Tabel Booking
6. `bookings` — header booking dengan `kode_booking`
7. `booking_details` — detail sesi (studio, paket, waktu)
8. `payments` — pembayaran (booking atau edit foto)

#### Tabel Edit Foto
9. `photo_edit_requests` — request edit dengan `kode_request`
10. `edit_request_items` — daftar foto yang diminta edit
11. `edited_photos` — hasil edit yang diupload ke Drive
12. `edit_status_logs` — riwayat perubahan status
13. `photo_deliveries` — pengiriman via WhatsApp
14. `delivery_items` — foto yang dikirim per delivery

#### Tabel Settings (Baru)
```
SETTINGS {
  int id_setting PK
  varchar key (unique) — mis: "qris_image", "wa_template_booking_pending"
  text value — path gambar atau template teks
  varchar tipe — "image", "text", "json"
  text deskripsi
  datetime updated_at
}
```

**Konvensi:**
- PK & FK: `id_*` (ikuti ERD, bukan standar Laravel)
- Uang: `DECIMAL(12,2)`
- No. HP: `VARCHAR(20)` format `628xxx`
- Status: `VARCHAR(30)` di-cast ke Enum PHP
- Foreign key dengan constraint

---

## 4. Keputusan Penting (Sudah Disetujui)

### Database & Struktur
✅ Penamaan kolom: `id_*` (ikuti ERD)  
✅ Nama folder: `project-clicked-booking`  
✅ Tabel `settings` baru untuk QRIS & template WA  

### Booking & Pembayaran
✅ Step 3: hanya upload bukti (nominal & tanggal auto)  
✅ Pembayaran penuh (tanpa DP)  
✅ **Notifikasi WhatsApp untuk booking (3 pesan):**
   1. Struk pending — saat upload bukti
   2. Notifikasi tolak — jika admin tolak bukti
   3. Struk konfirmasi — jika admin terima
✅ Bukti ditolak → pelanggan upload ulang (tanpa booking baru)

### Edit Foto
✅ Edit foto GRATIS (tanpa pembayaran terpisah)  
✅ Foto dipilih admin (pelanggan hanya isi instruksi)  
✅ Akses form dari mana saja (tanpa validasi lokasi)  
✅ Captcha: defer untuk nanti  

### Integrasi Eksternal
✅ Google Drive: Service Account authentication  
✅ Link folder Drive: public (siapa saja dengan link)  
✅ WhatsApp provider: defer detail implementasi  

### Dashboard & Lainnya
✅ Jumlah pelanggan: dari booking saja (bukan edit foto)  
✅ Hosting: defer untuk nanti  
✅ Domain final: defer untuk nanti  
✅ Retensi foto: defer untuk nanti  

---

## 5. Stack Teknologi

### Backend
- **Framework:** Laravel 12
- **PHP:** 8.2+
- **Database:** MySQL (production), driver `database` untuk queue
- **Testing:** PHPUnit (bisa migrasi ke Pest)
- **Code Style:** Laravel Pint (bawaan)
- **Queue:** Database driver, worker tergantung hosting

### Frontend
- **Template:** Blade
- **CSS:** Tailwind CSS v4 (via Vite)
- **Build:** Vite 7
- **JS Framework:** Alpine.js atau Livewire (belum diputuskan di PRD FE)

### External Services
- **Storage:** Google Drive (Service Account)
- **Messaging:** WhatsApp (provider TBD)
- **Captcha:** Defer untuk nanti

---

## 6. Rencana Sesi (BE & FE Bergantian)

```
Sesi 0 (selesai) 
  ↓
BE-1: Fondasi backend, 14 migrasi, model, seeder, guard admin
  ↓
FE-1: Tailwind setup, layout, komponen dasar, login admin
  ↓
BE-2: Booking wizard, slot, pembayaran, notifikasi WA, kelola studio
  ↓
FE-2: Landing, studio, wizard, status, admin booking
  ↓
BE-3: Edit foto core, matching booking, transisi status, QR
  ↓
FE-3: Form edit foto, status, admin antrian request
  ↓
BE-4: Drive, WhatsApp queue, webhook, status API
  ↓
FE-4: Upload hasil, dialog kirim WA, update status
  ↓
BE-5: Dashboard, kelola admin, pengaturan, pengerasan
  ↓
FE-5: Dashboard, kelola admin, pengaturan, polish
  ↓
Akhir: Review keamanan, tes lengkap, dokumentasi deployment
```

---

## 7. Nilai Usulan (Perlu Konfirmasi di Sesi Terkait)

Nilai berikut adalah **usulan PRD**, bukan keputusan final:

### Timing & Limits
- Jendela matching booking untuk edit foto: jam mulai → 24 jam setelah selesai
- Batas booking pending belum bayar: 6 jam
- Rentang booking: maksimal 90 hari ke depan
- Foto per request edit: maksimal 50
- Ukuran bukti bayar: 5 MB
- Ukuran foto hasil edit: 25 MB per file

### Retry & Throttle
- Retry WhatsApp: 3x dengan jeda 1, 5, 15 menit
- Throttle booking: 5 per menit per IP
- Throttle edit foto: 5 per 10 menit per IP dan per no. WA
- Throttle cek status: 20 per menit per IP
- Throttle login: 5 per menit

### Lain-lain
- Password admin: minimal 10 karakter
- Format kode: `BK-` dan `ED-` + 8 karakter dari 32 simbol
- Target cakupan tes Service: 80%

**Catatan:** Nilai-nilai ini akan dikonfirmasi saat implementasi di sesi yang relevan.

---

## 8. Hal yang Di-Defer

**Keputusan ini akan dibahas saat diperlukan:**

1. Provider WhatsApp & implementasi (API vs link pre-filled)
2. Provider Captcha untuk form edit foto
3. Hosting environment & queue worker setup
4. Domain final untuk generate QR code
5. Retention policy foto di Google Drive
6. Cara admin diberi tahu ada request baru (notifikasi)
7. Cara pelanggan memilih foto yang diedit (pending diskusi dengan client)
8. Pendekatan FE: Alpine.js vs Livewire (PRD FE section 3.2)
9. Warna brand & logo studio (PRD FE)

---

## 9. File yang Dibuat di Sesi 0

1. `docs/logs/DECISIONS.md` — dokumentasi keputusan (append-only)
2. `PRD/erd_studio_foto_v4.mermaid` — ERD dengan tabel `settings`
3. `docs/logs/be/SESSION-0-ANALYSIS.md` — dokumen ini

---

## 10. Action Items untuk BE-1

Setelah Sesi 0 disetujui, BE-1 akan mengerjakan:

### Setup Project
- [ ] Ubah `.env`: MySQL connection, locale `id`, timezone `Asia/Jakarta`
- [ ] Install Pest (opsional, atau tetap PHPUnit)
- [ ] Setup guard admin (config `auth.php`)

### Database
- [ ] Buat 14 migrasi sesuai ERD v4 (urutan benar, FK dengan constraint)
- [ ] Buat 6 Enum: `BookingStatus`, `PaymentStatus`, `EditStatus`, `DeliveryStatus`, `RequestSource`, `RoomStatus`
- [ ] Buat 14 Model dengan relasi lengkap
- [ ] Buat Factory & Seeder (admin pertama, contoh studio & paket)

### Dokumentasi
- [ ] Buat `docs/be/view-contracts.md` (awal, akan diupdate setiap sesi)
- [ ] Buat struktur log: `docs/logs/be/WORKLOG.md`

### Testing
- [ ] Setup testing dengan `RefreshDatabase`
- [ ] Buat tes dasar (migrasi lulus, seeder lulus, relasi model)

---

## 11. Aturan Kerja BE-1 dan Seterusnya

### Wajib Dipatuhi
1. **Tidak ada pengujian manual** — verifikasi lewat tes otomatis
2. **Bertahap** — satu sesi sekali, tidak berasumsi
3. **Persetujuan eksplisit** sebelum lanjut sesi berikutnya
4. **Log selama bekerja** — bukan di akhir
5. **Debug sebelum lapor** — protokol di PRD section 11
6. **Zona BE saja** — jangan sentuh `resources/views/` (kecuali stub 1 baris)

### Format Log
```markdown
### [2026-10-14 21:30 WIB] BE-1 judul singkat
- Jenis: tindakan/tes/debug/pertanyaan/stub
- File: path
- Alasan: ...
- Verifikasi: perintah dan hasil
```

### Laporan Sesi
Setiap sesi diakhiri laporan `docs/logs/be/report-BE-N.md` dengan:
- Ringkasan
- File dibuat/diubah
- Tes yang dijalankan
- Bug yang ditemukan & diperbaiki
- Tidak dikerjakan & alasannya
- Pertanyaan terbuka
- Penyimpangan dari PRD/ERD (jika ada, minta persetujuan)
- Perubahan kontrak untuk FE
- Sesi berikutnya yang diusulkan
- **Menunggu persetujuan** (closing statement)

---

## 12. Kesimpulan Sesi 0

✅ **Dokumen dibaca & dipahami:** PRD BE, PRD FE, ERD v3  
✅ **Project dianalisis:** Laravel 12 fresh install  
✅ **Pertanyaan diajukan & dijawab:** 17 keputusan dicatat  
✅ **ERD v4 dibuat:** tambahan tabel `settings`  
✅ **Struktur dokumentasi disiapkan:** `DECISIONS.md`, analisis ini  

**Status:** Sesi 0 selesai.

**Langkah berikutnya:** Menunggu persetujuan untuk melanjutkan ke **Sesi BE-1** (Fondasi Backend).

---

**Dibuat:** 2026-10-14 21:20 WIB  
**Agent:** Kiro (Backend)