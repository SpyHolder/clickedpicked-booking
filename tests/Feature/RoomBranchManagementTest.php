<?php

namespace Tests\Feature;

use App\Enums\RoomStatus;
use App\Models\Admin;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoomBranchManagementTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = Admin::create([
            'nama' => 'Admin Studio',
            'email' => 'admin@clicked.studio',
            'password' => Hash::make('password123'),
            'no_hp' => '6281111111111',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_view_studio_split_by_cabang_tabs(): void
    {
        $roomBatu = Room::create([
            'nama_room' => 'Room 2',
            'cabang' => 'batu_lima',
            'jenis_room' => 'photo_studio',
            'kapasitas' => 8,
            'status' => RoomStatus::AKTIF->value,
        ]);

        $roomMerdeka = Room::create([
            'nama_room' => 'Room 1',
            'cabang' => 'jalan_merdeka',
            'jenis_room' => 'photo_booth',
            'kapasitas' => 4,
            'status' => RoomStatus::AKTIF->value,
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.studio.index'));

        $response->assertOk()
            ->assertSee('Cabang Batu 5')
            ->assertSee('Cabang Jalan Merdeka')
            ->assertSee('Photo Studio')
            ->assertSee('Photo Booth')
            ->assertSee('Room 2')
            ->assertSee('Room 1');
    }

    public function test_admin_can_create_room_with_cabang_and_jenis_room(): void
    {
        $payload = [
            'nama_room' => 'Room 3',
            'cabang' => 'batu_lima',
            'jenis_room' => 'photo_studio',
            'kapasitas' => 10,
            'deskripsi' => 'Photo Studio kapasitas besar',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.studio.store'), $payload);

        $response->assertRedirect(route('admin.studio.index', ['tab' => 'batu_lima']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'nama_room' => 'Room 3',
            'cabang' => 'batu_lima',
            'jenis_room' => 'photo_studio',
            'kapasitas' => 10,
        ]);

        $room = Room::where('nama_room', 'Room 3')->first();
        $this->assertEquals('Batu 5', $room->cabang_label);
        $this->assertEquals('Photo Studio', $room->jenis_room_label);
        $this->assertTrue($room->isBookableStudio());
        $this->assertFalse($room->isPhotoBooth());
    }

    public function test_admin_can_create_merdeka_photo_booth_without_manual_typing(): void
    {
        $payload = [
            'nama_room' => 'Room 2',
            'cabang' => 'jalan_merdeka',
            'jenis_room' => 'photo_booth',
            'kapasitas' => 4,
            'deskripsi' => 'Photo booth walk-in Merdeka',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.studio.store'), $payload);

        $response->assertRedirect(route('admin.studio.index', ['tab' => 'jalan_merdeka']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('rooms', [
            'nama_room' => 'Room 2',
            'cabang' => 'jalan_merdeka',
            'jenis_room' => 'photo_booth',
        ]);

        $room = Room::where('nama_room', 'Room 2')->where('cabang', 'jalan_merdeka')->first();
        $this->assertEquals('Jalan Merdeka', $room->cabang_label);
        $this->assertEquals('Photo Booth', $room->jenis_room_label);
        $this->assertTrue($room->isPhotoBooth());
        $this->assertFalse($room->isBookableStudio());
    }

    public function test_admin_can_update_room_cabang_and_jenis_room(): void
    {
        $room = Room::create([
            'nama_room' => 'Room 1',
            'cabang' => 'batu_lima',
            'jenis_room' => 'photo_booth',
            'kapasitas' => 4,
            'status' => RoomStatus::AKTIF->value,
        ]);

        $payload = [
            'nama_room' => 'Room 1 Super',
            'cabang' => 'jalan_merdeka',
            'jenis_room' => 'photo_booth',
            'kapasitas' => 5,
            'deskripsi' => 'Dipindahkan ke Jalan Merdeka',
        ];

        $response = $this->actingAs($this->admin, 'admin')
            ->put(route('admin.studio.update', $room->id_room), $payload);

        $response->assertRedirect(route('admin.studio.index', ['tab' => 'jalan_merdeka']))
            ->assertSessionHas('success');

        $room->refresh();
        $this->assertEquals('Room 1 Super', $room->nama_room);
        $this->assertEquals('jalan_merdeka', $room->cabang);
        $this->assertEquals('Jalan Merdeka', $room->cabang_label);
        $this->assertEquals(5, $room->kapasitas);
    }

    public function test_public_landing_and_studio_pages_render_taste_skill_design(): void
    {
        Room::create([
            'nama_room' => 'Room 2',
            'cabang' => 'batu_lima',
            'jenis_room' => 'photo_studio',
            'kapasitas' => 8,
            'status' => RoomStatus::AKTIF->value,
        ]);

        Room::create([
            'nama_room' => 'Room 1',
            'cabang' => 'jalan_merdeka',
            'jenis_room' => 'photo_booth',
            'kapasitas' => 4,
            'status' => RoomStatus::AKTIF->value,
        ]);

        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk()
            ->assertSee('data-type="place"', false)
            ->assertSee('data-enter', false)
            ->assertSee('data-moment="picture-develops"', false)
            ->assertSee('Cabang Batu 5')
            ->assertSee('Cabang Jalan Merdeka')
            ->assertSee('photo-f39702528-1280.jpg', false);

        $studioResponse = $this->get(route('studio.index'));
        $studioResponse->assertOk()
            ->assertSee('Cabang Batu 5')
            ->assertSee('Cabang Jalan Merdeka')
            ->assertSee('Bisa Booking')
            ->assertSee('Walk-In Only');
    }
}
