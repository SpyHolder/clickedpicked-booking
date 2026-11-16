<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Frame;
use App\Models\Room;
use App\Models\RoomPackage;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed: admin pertama, cabang Merdeka (3 Photo Booth — Walk-In Only) &
     * cabang Batu Lima (Room 1 & 4 Photo Booth Walk-In, Room 2 & 3 Photo Studio — Bisa Booking & Pilih Frame),
     * katalog frame beserta gambar contoh template foto, dan pengaturan dasar.
     */
    public function run(): void
    {
        // Admin pertama
        Admin::updateOrCreate(
            ['email' => 'admin@clicked.studio'],
            [
                'nama' => 'Admin Clicked',
                'password' => Hash::make('adminclicked'),
                'no_hp' => '6281234567890',
                'is_active' => true,
            ]
        );

        // ==========================================
        // CABANG BATU 5 — PHOTO STUDIO (BISA DI-BOOKING ONLINE & PILIH FRAME)
        // ==========================================
        $studioRooms = [
            [
                'cabang' => 'batu_lima',
                'nama_room' => 'Room 2',
                'jenis_room' => 'photo_studio',
                'deskripsi' => 'Photo Studio cabang Batu 5 (Room 2). Dapat di-booking online. Seluruh foto sesi (~80 foto) masuk ke Google Drive, pelanggan bebas memilih Template Frame & kode foto sesuai jumlah slot.',
                'kapasitas' => 8,
            ],
            [
                'cabang' => 'batu_lima',
                'nama_room' => 'Room 3',
                'jenis_room' => 'photo_studio',
                'deskripsi' => 'Photo Studio cabang Batu 5 (Room 3). Ruang lebih luas untuk grup/keluarga, dapat di-booking online. Hasil foto masuk ke Google Drive + layanan pilih Frame & Custom Frame.',
                'kapasitas' => 10,
            ],
        ];

        foreach ($studioRooms as $data) {
            $room = Room::create(array_merge($data, ['status' => 'aktif']));

            RoomPackage::create([
                'id_room' => $room->id_room,
                'harga' => 120000,
                'tipe_harga' => 'per_sesi',
                'durasi_menit' => 30,
                'deskripsi_benefit' => 'Sesi Photo Studio 30 menit (~80 foto di Google Drive) + Gratis Pilih Frame / Edit Foto',
                'is_active' => true,
            ]);
            RoomPackage::create([
                'id_room' => $room->id_room,
                'harga' => 200000,
                'tipe_harga' => 'per_sesi',
                'durasi_menit' => 60,
                'deskripsi_benefit' => 'Sesi Photo Studio 60 menit (Semua file di Google Drive) + Gratis Pilih Frame & Custom Frame',
                'is_active' => true,
            ]);
        }

        // ==========================================
        // CABANG BATU 5 & JALAN MERDEKA — PHOTO BOOTH (WALK-IN ONLY / TIDAK BISA DI-BOOKING)
        // ==========================================
        $photoBoothRooms = [
            [
                'cabang' => 'batu_lima',
                'nama_room' => 'Room 1',
                'jenis_room' => 'photo_booth',
                'deskripsi' => 'Photo Booth cabang Batu 5 (Room 1). Khusus Walk-In (langsung datang tanpa booking). Pemilihan frame dilakukan langsung di layar PC dalam ruangan.',
                'kapasitas' => 4,
            ],
            [
                'cabang' => 'batu_lima',
                'nama_room' => 'Room 4',
                'jenis_room' => 'photo_booth',
                'deskripsi' => 'Photo Booth cabang Batu 5 (Room 4). Khusus Walk-In (langsung datang tanpa booking). Pemilihan frame dilakukan langsung di layar PC dalam ruangan.',
                'kapasitas' => 4,
            ],
            [
                'cabang' => 'jalan_merdeka',
                'nama_room' => 'Room 1',
                'jenis_room' => 'photo_booth',
                'deskripsi' => 'Self-Photo Booth cabang Jalan Merdeka (Room 1). Khusus Walk-In (tidak menggunakan sistem booking online), pilih frame otomatis langsung di layar PC booth.',
                'kapasitas' => 4,
            ],
            [
                'cabang' => 'jalan_merdeka',
                'nama_room' => 'Room 2',
                'jenis_room' => 'photo_booth',
                'deskripsi' => 'Self-Photo Booth cabang Jalan Merdeka (Room 2). Khusus Walk-In (tidak menggunakan sistem booking online), pilih frame otomatis langsung di layar PC booth.',
                'kapasitas' => 4,
            ],
            [
                'cabang' => 'jalan_merdeka',
                'nama_room' => 'Room 3',
                'jenis_room' => 'photo_booth',
                'deskripsi' => 'Self-Photo Booth cabang Jalan Merdeka (Room 3). Khusus Walk-In (tidak menggunakan sistem booking online), pilih frame otomatis langsung di layar PC booth.',
                'kapasitas' => 5,
            ],
        ];

        foreach ($photoBoothRooms as $data) {
            Room::create(array_merge($data, ['status' => 'aktif']));
        }

        // ==========================================
        // KATALOG FRAME BESERTA CONTOH TEMPLATE FOTO (Untuk Photo Studio Room 2 & Room 3 Batu Lima)
        // ==========================================
        $frames = [
            [
                'kode_frame' => 'FR-01',
                'nama_frame' => 'Minimalist Cream Duo',
                'jumlah_slot' => 2,
                'warna_aksen' => '#f3f0e8',
                'preview_path' => 'frames/fr-01-minimalist-cream.svg',
                'deskripsi' => 'Frame 2 slot vertikal bernuansa krem bersih khas Clicked Studio.',
                'is_active' => true,
            ],
            [
                'kode_frame' => 'FR-02',
                'nama_frame' => 'Classic Noir Duo',
                'jumlah_slot' => 2,
                'warna_aksen' => '#172033',
                'preview_path' => 'frames/fr-02-classic-noir.svg',
                'deskripsi' => 'Frame 2 slot kontras gelap elegan untuk potret monokrom maupun warna.',
                'is_active' => true,
            ],
            [
                'kode_frame' => 'FR-03',
                'nama_frame' => 'Editorial Strip Trio',
                'jumlah_slot' => 3,
                'warna_aksen' => '#7b2631',
                'preview_path' => 'frames/fr-03-editorial-trio.svg',
                'deskripsi' => 'Photo strip klasik 3 slot foto berurutan dengan aksen maroon.',
                'is_active' => true,
            ],
            [
                'kode_frame' => 'FR-04',
                'nama_frame' => 'Retro Film 3-Slot',
                'jumlah_slot' => 3,
                'warna_aksen' => '#231f20',
                'preview_path' => 'frames/fr-04-retro-film.svg',
                'deskripsi' => 'Frame 3 slot bergaya rol film analog dengan border hangat.',
                'is_active' => true,
            ],
            [
                'kode_frame' => 'FR-05',
                'nama_frame' => 'Signature Grid Quad',
                'jumlah_slot' => 4,
                'warna_aksen' => '#163b71',
                'preview_path' => 'frames/fr-05-signature-quad.svg',
                'deskripsi' => 'Kolase 4 slot foto (2x2 grid) favorit untuk foto grup dan sahabat.',
                'is_active' => true,
            ],
            [
                'kode_frame' => 'FR-06',
                'nama_frame' => 'Storybook Six Collage',
                'jumlah_slot' => 6,
                'warna_aksen' => '#cbbb9e',
                'preview_path' => 'frames/fr-06-storybook-six.svg',
                'deskripsi' => 'Frame 6 slot foto (2x3 grid) untuk merangkum banyak ekspresi sekaligus.',
                'is_active' => true,
            ],
        ];

        foreach ($frames as $frameData) {
            Frame::updateOrCreate(['kode_frame' => $frameData['kode_frame']], $frameData);
        }

        // Pengaturan awal
        Setting::setValue('qris_image', '', 'image', 'Path gambar QRIS untuk pembayaran');
        Setting::setValue('wa_template_booking_pending', 'Halo {nama}, booking Anda ({kode_booking}) sebesar Rp {total_harga} sedang menunggu verifikasi pembayaran.', 'text', 'Template WA saat bukti bayar diupload');
        Setting::setValue('wa_template_booking_confirmed', 'Halo {nama}, pembayaran booking Anda ({kode_booking}) telah dikonfirmasi. Selamat menikmati sesi foto!', 'text', 'Template WA saat booking dikonfirmasi');
        Setting::setValue('wa_template_booking_rejected', 'Halo {nama}, bukti pembayaran booking Anda ({kode_booking}) ditolak. Silakan upload ulang bukti pembayaran.', 'text', 'Template WA saat bukti bayar ditolak');
        Setting::setValue('wa_template_edit_delivery', 'Halo {nama}, hasil edit foto & frame Anda ({kode_request}) sudah selesai. Silakan cek & unduh di: {link_drive}', 'text', 'Template WA saat pengiriman hasil edit foto');
    }
}
