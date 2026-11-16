<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\EditStatus;
use App\Enums\RoomStatus;
use App\Models\Admin;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Models\EditedPhoto;
use App\Models\PhotoEditRequest;
use App\Models\Room;
use App\Models\RoomPackage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PhotoEditTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;
    private Room $activeRoom;
    private RoomPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nama' => 'Admin Editor 1',
            'email' => 'editor1@clicked.studio',
            'password' => Hash::make('password123'),
            'no_hp' => '6281111111111',
            'is_active' => true,
        ]);

        $this->activeRoom = Room::create([
            'nama_room' => 'Studio Utama',
            'jenis_room' => 'Indoor',
            'deskripsi' => 'Studio self-photo indoor',
            'kapasitas' => 6,
            'status' => RoomStatus::AKTIF->value,
        ]);

        $this->package = RoomPackage::create([
            'id_room' => $this->activeRoom->id_room,
            'harga' => 150000,
            'tipe_harga' => 'per_sesi',
            'durasi_menit' => 60,
            'deskripsi_benefit' => 'Sesi 60 menit',
            'is_active' => true,
        ]);
    }

    public function test_qr_request_matches_active_booking_and_allows_multiple_requests(): void
    {
        $booking = Booking::create([
            'kode_booking' => 'BK-1234ABCD',
            'nama_pelanggan' => 'Budi Santoso',
            'no_hp_pelanggan' => '6281234567890',
            'tanggal_booking' => now()->toDateString(),
            'status_booking' => BookingStatus::CONFIRMED->value,
            'total_harga' => 150000,
        ]);

        $detail = BookingDetail::create([
            'id_booking' => $booking->id_booking,
            'id_room' => $this->activeRoom->id_room,
            'id_package' => $this->package->id_package,
            'tanggal_sesi' => now()->toDateString(),
            'jam_mulai' => now()->subHour()->format('H:i'),
            'jam_selesai' => now()->format('H:i'),
            'jumlah_orang' => 2,
            'harga' => 150000,
        ]);

        $payload = [
            'nama_pelanggan' => 'Budi Santoso',
            'nomor_wa' => '0812-3456-7890',
            'nomor_wa_confirmation' => '081234567890',
            'items' => [
                ['nama_file' => 'IMG_0010', 'instruksi_edit' => 'Cerahkan wajah'],
                ['nama_file' => 'IMG_0015', 'instruksi_edit' => 'Tone hangat'],
            ],
            'catatan' => 'Mohon natural',
            'persetujuan_data' => '1',
            'website' => '',
        ];

        $response1 = $this->post(route('edit-foto.store', $this->activeRoom->id_room), $payload);
        $response1->assertRedirect();

        $req1 = PhotoEditRequest::first();
        $this->assertNotNull($req1);
        $this->assertStringStartsWith('ED-', $req1->kode_request);
        $this->assertEquals($booking->id_booking, $req1->id_booking);
        $this->assertEquals($detail->id_detail, $req1->id_detail);
        $this->assertFalse($req1->perlu_dicek);
        $this->assertCount(2, $req1->items);

        // Request kedua pada booking yang sama harus tetap berhasil
        $response2 = $this->post(route('edit-foto.store', $this->activeRoom->id_room), $payload);
        $response2->assertRedirect();
        $this->assertEquals(2, PhotoEditRequest::where('id_booking', $booking->id_booking)->count());
    }

    public function test_qr_request_without_matching_booking_sets_perlu_dicek_true(): void
    {
        $payload = [
            'nama_pelanggan' => 'Siti Aminah',
            'nomor_wa' => '081398765432',
            'nomor_wa_confirmation' => '081398765432',
            'items' => [
                ['nama_file' => 'DSC_0099', 'instruksi_edit' => ''],
            ],
            'persetujuan_data' => '1',
            'website' => '',
        ];

        $response = $this->post(route('edit-foto.store', $this->activeRoom->id_room), $payload);
        $response->assertRedirect();

        $req = PhotoEditRequest::first();
        $this->assertNotNull($req);
        $this->assertNull($req->id_booking);
        $this->assertTrue($req->perlu_dicek);
    }

    public function test_inactive_room_rejects_qr_submission(): void
    {
        $inactiveRoom = Room::create([
            'nama_room' => 'Studio Nonaktif',
            'jenis_room' => 'Outdoor',
            'kapasitas' => 4,
            'status' => RoomStatus::NONAKTIF->value,
        ]);

        $getResponse = $this->get(route('edit-foto.form', $inactiveRoom->id_room));
        $getResponse->assertStatus(200);
        $getResponse->assertSee('Formulir Tidak Tersedia');

        $postResponse = $this->post(route('edit-foto.store', $inactiveRoom->id_room), [
            'nama_pelanggan' => 'Andi',
            'nomor_wa' => '081234567890',
            'nomor_wa_confirmation' => '081234567890',
            'items' => [['nama_file' => 'IMG_01']],
            'persetujuan_data' => '1',
        ]);

        $postResponse->assertSessionHas('error');
        $this->assertEquals(0, PhotoEditRequest::count());
    }

    public function test_public_status_page_masks_phone_and_hides_drive_link(): void
    {
        $editRequest = PhotoEditRequest::create([
            'kode_request' => 'ED-TEST9988',
            'id_room' => $this->activeRoom->id_room,
            'sumber' => 'qr',
            'nama_pelanggan' => 'Rina',
            'nomor_wa' => '6281234567890',
            'perlu_dicek' => false,
            'persetujuan_data' => true,
            'status_edit' => EditStatus::DIKIRIM->value,
            'drive_folder_id' => 'folder_secret_123',
            'drive_folder_url' => 'https://drive.google.com/drive/folders/secret_123',
        ]);

        // Test redirect dari /check-status
        $checkRes = $this->post(route('check-status.process'), ['kode' => 'ed-test9988']);
        $checkRes->assertRedirect(route('edit-foto.status', 'ED-TEST9988'));

        // Test halaman status publik
        $statusRes = $this->get(route('edit-foto.status', 'ED-TEST9988'));
        $statusRes->assertStatus(200);
        $statusRes->assertSee('ED-TEST9988');
        $statusRes->assertSee('62812****7890');
        $statusRes->assertDontSee('6281234567890');
        $statusRes->assertDontSee('https://drive.google.com/drive/folders/secret_123');
    }

    public function test_status_transitions_atomic_claim_and_reopen(): void
    {
        $admin2 = Admin::create([
            'nama' => 'Admin Editor 2',
            'email' => 'editor2@clicked.studio',
            'password' => Hash::make('password123'),
            'no_hp' => '6282222222222',
            'is_active' => true,
        ]);

        $editRequest = PhotoEditRequest::create([
            'kode_request' => 'ED-FLOW1234',
            'id_room' => $this->activeRoom->id_room,
            'sumber' => 'qr',
            'nama_pelanggan' => 'Dimas',
            'nomor_wa' => '6281234567899',
            'perlu_dicek' => true,
            'persetujuan_data' => true,
            'status_edit' => EditStatus::MENUNGGU_VERIFIKASI->value,
        ]);

        // 1. Terima request (menunggu_verifikasi -> pending)
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.accept', $editRequest->id_request))
            ->assertSessionHas('success');

        $editRequest->refresh();
        $this->assertEquals(EditStatus::PENDING, $editRequest->status_edit);
        $this->assertEquals($this->admin->id_admin, $editRequest->verified_by);

        // 2. Klaim atomik oleh Admin 1 berhasil, Admin 2 gagal
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.claim', $editRequest->id_request))
            ->assertSessionHas('success');

        $this->actingAs($admin2, 'admin')
            ->post(route('admin.edit-foto.claim', $editRequest->id_request))
            ->assertSessionHas('error');

        $editRequest->refresh();
        $this->assertEquals(EditStatus::DIPROSES, $editRequest->status_edit);
        $this->assertEquals($this->admin->id_admin, $editRequest->handled_by);

        // 3. Tandai selesai tanpa foto di Drive harus ditolak
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.complete', $editRequest->id_request))
            ->assertSessionHas('error');

        $this->assertEquals(EditStatus::DIPROSES, $editRequest->fresh()->status_edit);

        // Tambahkan 1 foto yang sudah terunggah ke Drive
        EditedPhoto::create([
            'id_request' => $editRequest->id_request,
            'nama_file' => 'IMG_0010_edit.jpg',
            'drive_file_id' => 'gdrive_file_001',
            'drive_url' => 'https://drive.google.com/file/d/gdrive_file_001/view',
            'mime_type' => 'image/jpeg',
            'ukuran_file' => 204800,
            'uploaded_at' => now(),
        ]);

        // Sekarang Tandai Selesai berhasil (diproses -> selesai)
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.complete', $editRequest->id_request))
            ->assertSessionHas('success');

        $this->assertEquals(EditStatus::SELESAI, $editRequest->fresh()->status_edit);

        // Buka kembali dari status 'selesai' harus ditolak (hanya boleh dari 'dikirim')
        $this->actingAs($admin2, 'admin')
            ->post(route('admin.edit-foto.reopen', $editRequest->id_request))
            ->assertSessionHas('error');

        // Simulasikan sudah terkirim -> buka kembali oleh Admin 2 berhasil
        $editRequest->update(['status_edit' => EditStatus::DIKIRIM->value]);

        $this->actingAs($admin2, 'admin')
            ->post(route('admin.edit-foto.reopen', $editRequest->id_request))
            ->assertSessionHas('success');

        $editRequest->refresh();
        $this->assertEquals(EditStatus::DIPROSES, $editRequest->status_edit);
        $this->assertEquals($admin2->id_admin, $editRequest->handled_by);
    }

    public function test_returning_customer_banner_displays_on_admin_detail(): void
    {
        // Buat 1 booking & 1 request sebelumnya dengan nomor WA yang sama
        Booking::create([
            'kode_booking' => 'BK-PREV0001',
            'nama_pelanggan' => 'Putri',
            'no_hp_pelanggan' => '6281299998888',
            'tanggal_booking' => now()->subDays(5)->toDateString(),
            'status_booking' => BookingStatus::DONE->value,
            'total_harga' => 150000,
        ]);

        PhotoEditRequest::create([
            'kode_request' => 'ED-PREV0001',
            'id_room' => $this->activeRoom->id_room,
            'sumber' => 'qr',
            'nama_pelanggan' => 'Putri',
            'nomor_wa' => '6281299998888',
            'status_edit' => EditStatus::DIKIRIM->value,
        ]);

        $currentReq = PhotoEditRequest::create([
            'kode_request' => 'ED-CURR0002',
            'id_room' => $this->activeRoom->id_room,
            'sumber' => 'qr',
            'nama_pelanggan' => 'Putri',
            'nomor_wa' => '6281299998888',
            'status_edit' => EditStatus::MENUNGGU_VERIFIKASI->value,
        ]);

        $res = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.edit-foto.show', $currentReq->id_request));

        $res->assertStatus(200);
        $res->assertSee('Pelanggan kembali: 1 request dan 1 booking sebelumnya');
        $res->assertSee('BK-PREV0001');
        $res->assertSee('ED-PREV0001');
    }

    public function test_frame_selection_and_admin_photo_upload_and_wa_delivery(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $frame = \App\Models\Frame::create([
            'kode_frame' => 'FR-TEST02',
            'nama_frame' => 'Studio Duo Frame',
            'jumlah_slot' => 2,
            'is_active' => true,
        ]);

        // 1. Pelanggan submit QR dengan memilih Frame FR-TEST02 (2 slot) dan 2 kode foto dari Drive
        $res = $this->post(route('edit-foto.store', $this->activeRoom->id_room), [
            'nama_pelanggan' => 'Sinta Studio',
            'nomor_wa' => '081277776666',
            'nomor_wa_confirmation' => '081277776666',
            'jenis_layanan' => 'foto_frame',
            'id_frame' => $frame->id_frame,
            'jumlah_slot' => 2,
            'items' => [
                ['nama_file' => 'Kode 1', 'instruksi_edit' => 'Slot atas'],
                ['nama_file' => 'Kode 2', 'instruksi_edit' => 'Slot bawah'],
            ],
            'persetujuan_data' => '1',
        ]);

        $req = PhotoEditRequest::where('nama_pelanggan', 'Sinta Studio')->first();
        $this->assertNotNull($req);
        $this->assertEquals('foto_frame', $req->jenis_layanan);
        $this->assertEquals($frame->id_frame, $req->id_frame);
        $this->assertEquals(2, $req->jumlah_slot);

        // 2. Admin terima & ambil request (menunggu_verifikasi -> pending -> diproses)
        $this->actingAs($this->admin, 'admin')->post(route('admin.edit-foto.accept', $req->id_request));
        $this->actingAs($this->admin, 'admin')->post(route('admin.edit-foto.claim', $req->id_request));
        $this->assertEquals(EditStatus::DIPROSES, $req->fresh()->status_edit);

        // 3. Admin upload hasil Foto + Frame
        $fakeFile = \Illuminate\Http\UploadedFile::fake()->create('hasil_frame_sinta.jpg', 500, 'image/jpeg');
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.upload', $req->id_request), [
                'kategori_hasil' => 'foto_frame',
                'photos' => [$fakeFile],
            ])
            ->assertSessionHas('success');

        $this->assertEquals(1, $req->editedPhotos()->count());
        $this->assertEquals('foto_frame', $req->editedPhotos()->first()->kategori_hasil);

        // 4. Admin tandai selesai & kirim WA
        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.complete', $req->id_request))
            ->assertSessionHas('success');
        $this->assertEquals(EditStatus::SELESAI, $req->fresh()->status_edit);

        $this->actingAs($this->admin, 'admin')
            ->post(route('admin.edit-foto.send-wa', $req->id_request), [
                'pesan' => 'Halo Sinta, ini hasil Foto + Frame kamu!',
            ])
            ->assertSessionHas('success')
            ->assertSessionHas('wa_redirect_url');

        $this->assertEquals(EditStatus::DIKIRIM, $req->fresh()->status_edit);
    }

    public function test_unauthenticated_guest_accessing_admin_redirects_to_admin_login(): void
    {
        $this->get('/admin')
            ->assertRedirect(route('admin.login'));

        $this->get('/admin/edit-foto')
            ->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_update_studio_package(): void
    {
        $package = \App\Models\RoomPackage::create([
            'id_room' => $this->activeRoom->id_room,
            'tipe_harga' => 'Paket Awal',
            'harga' => 100000,
            'durasi_menit' => 45,
            'deskripsi_benefit' => 'Benefit lama',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin, 'admin')
            ->put(route('admin.studio.packages.update', [$this->activeRoom->id_room, $package->id_package]), [
                'tipe_harga' => 'Paket Premium 60M',
                'harga' => 250000,
                'durasi_menit' => 60,
                'deskripsi_benefit' => 'Benefit baru lengkap',
                'is_active' => '1',
            ])
            ->assertSessionHas('success');

        $package->refresh();
        $this->assertEquals('Paket Premium 60M', $package->tipe_harga);
        $this->assertEquals(250000, (int) $package->harga);
        $this->assertEquals(60, $package->durasi_menit);
        $this->assertEquals('Benefit baru lengkap', $package->deskripsi_benefit);
        $this->assertTrue($package->is_active);
    }
}
