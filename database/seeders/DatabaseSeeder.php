<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Room;
use App\Models\RoomPackage;
use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed: admin pertama, contoh studio dan paket, dan pengaturan dasar.
     * Sesuai PRD BE-1.
     */
    public function run(): void
    {
        // Admin pertama
        Admin::create([
            'nama' => 'Admin Clicked',
            'email' => 'admin@clicked.studio',
            'password' => Hash::make('adminclicked'),
            'no_hp' => '6281234567890',
            'is_active' => true,
        ]);

        // Contoh studio
        $studio1 = Room::create([
            'nama_room' => 'Studio A',
            'jenis_room' => 'Indoor',
            'deskripsi' => 'Studio indoor dengan pencahayaan profesional dan backdrop polos.',
            'kapasitas' => 6,
            'status' => 'aktif',
        ]);

        $studio2 = Room::create([
            'nama_room' => 'Studio B',
            'jenis_room' => 'Outdoor',
            'deskripsi' => 'Area outdoor dengan pencahayaan alami dan latar taman.',
            'kapasitas' => 10,
            'status' => 'aktif',
        ]);

        // Paket untuk Studio A
        RoomPackage::create([
            'id_room' => $studio1->id_room,
            'harga' => 150000,
            'tipe_harga' => 'per_sesi',
            'durasi_menit' => 60,
            'deskripsi_benefit' => 'Sesi 1 jam, termasuk properti dasar',
            'is_active' => true,
        ]);

        RoomPackage::create([
            'id_room' => $studio1->id_room,
            'harga' => 250000,
            'tipe_harga' => 'per_sesi',
            'durasi_menit' => 120,
            'deskripsi_benefit' => 'Sesi 2 jam, termasuk properti lengkap dan backdrop warna',
            'is_active' => true,
        ]);

        // Paket untuk Studio B
        RoomPackage::create([
            'id_room' => $studio2->id_room,
            'harga' => 200000,
            'tipe_harga' => 'per_sesi',
            'durasi_menit' => 90,
            'deskripsi_benefit' => 'Sesi 1.5 jam, area outdoor eksklusif',
            'is_active' => true,
        ]);

        // Pengaturan awal
        Setting::setValue('qris_image', '', 'image', 'Path gambar QRIS untuk pembayaran');
        Setting::setValue('wa_template_booking_pending', 'Halo {nama}, booking Anda ({kode_booking}) sebesar Rp {total_harga} sedang menunggu verifikasi pembayaran.', 'text', 'Template WA saat bukti bayar diupload');
        Setting::setValue('wa_template_booking_confirmed', 'Halo {nama}, pembayaran booking Anda ({kode_booking}) telah dikonfirmasi. Selamat menikmati sesi foto!', 'text', 'Template WA saat booking dikonfirmasi');
        Setting::setValue('wa_template_booking_rejected', 'Halo {nama}, bukti pembayaran booking Anda ({kode_booking}) ditolak. Silakan upload ulang bukti pembayaran.', 'text', 'Template WA saat bukti bayar ditolak');
        Setting::setValue('wa_template_edit_delivery', 'Halo {nama}, hasil edit foto Anda ({kode_request}) sudah selesai. Silakan unduh di: {link_drive}', 'text', 'Template WA saat pengiriman hasil edit foto');
    }
}
