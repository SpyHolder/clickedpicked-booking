<?php

namespace App\Services;

use App\Models\Room;
use App\Models\RoomImage;
use App\Models\RoomPackage;
use App\Enums\RoomStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class RoomService
{
    /**
     * Daftar studio aktif untuk pelanggan.
     */
    public function getActiveRooms(): Collection
    {
        return Room::where('status', RoomStatus::AKTIF)
            ->with('images')
            ->get();
    }

    /**
     * Daftar semua studio (admin — termasuk nonaktif).
     */
    public function getAllRoomsAdmin(int $perPage = 15): LengthAwarePaginator
    {
        return Room::with('images')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Detail studio by id. Hanya studio aktif untuk publik.
     */
    public function getActiveRoomById(int $id): ?Room
    {
        return Room::where('id_room', $id)
            ->where('status', RoomStatus::AKTIF)
            ->with(['images', 'packages' => fn ($q) => $q->where('is_active', true)])
            ->first();
    }

    /**
     * Detail studio by id (admin, termasuk nonaktif).
     */
    public function getRoomById(int $id): ?Room
    {
        return Room::with(['images', 'packages'])->find($id);
    }

    /**
     * Buat studio baru. PRD: tidak ada hapus, hanya nonaktif.
     */
    public function createRoom(array $data): Room
    {
        return Room::create([
            'nama_room' => $data['nama_room'],
            'jenis_room' => $data['jenis_room'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'kapasitas' => $data['kapasitas'],
            'status' => RoomStatus::AKTIF->value,
        ]);
    }

    /**
     * Update studio.
     */
    public function updateRoom(int $id, array $data): ?Room
    {
        $room = Room::find($id);
        if (!$room) return null;

        $room->update([
            'nama_room' => $data['nama_room'] ?? $room->nama_room,
            'jenis_room' => $data['jenis_room'] ?? $room->jenis_room,
            'deskripsi' => $data['deskripsi'] ?? $room->deskripsi,
            'kapasitas' => $data['kapasitas'] ?? $room->kapasitas,
        ]);

        return $room;
    }

    /**
     * Toggle status studio (aktif ↔ nonaktif). PRD: tidak ada hapus.
     */
    public function toggleStatus(int $id): ?Room
    {
        $room = Room::find($id);
        if (!$room) return null;

        $newStatus = $room->status === RoomStatus::AKTIF
            ? RoomStatus::NONAKTIF
            : RoomStatus::AKTIF;

        $room->update(['status' => $newStatus->value]);
        return $room;
    }

    // ── Paket ──

    /**
     * Paket aktif untuk studio tertentu (publik).
     */
    public function getActivePackages(int $roomId): Collection
    {
        return RoomPackage::where('id_room', $roomId)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Semua paket untuk studio (admin).
     */
    public function getAllPackages(int $roomId): Collection
    {
        return RoomPackage::where('id_room', $roomId)->get();
    }

    /**
     * Buat paket baru.
     */
    public function createPackage(int $roomId, array $data): RoomPackage
    {
        return RoomPackage::create([
            'id_room' => $roomId,
            'harga' => $data['harga'],
            'tipe_harga' => $data['tipe_harga'],
            'durasi_menit' => $data['durasi_menit'],
            'deskripsi_benefit' => $data['deskripsi_benefit'] ?? null,
            'is_active' => true,
        ]);
    }

    /**
     * Update paket. Termasuk toggle is_active.
     */
    public function updatePackage(int $packageId, array $data): ?RoomPackage
    {
        $package = RoomPackage::find($packageId);
        if (!$package) return null;

        $package->update([
            'harga' => $data['harga'] ?? $package->harga,
            'tipe_harga' => $data['tipe_harga'] ?? $package->tipe_harga,
            'durasi_menit' => $data['durasi_menit'] ?? $package->durasi_menit,
            'deskripsi_benefit' => $data['deskripsi_benefit'] ?? $package->deskripsi_benefit,
            'is_active' => $data['is_active'] ?? $package->is_active,
        ]);

        return $package;
    }

    // ── Gambar studio ──

    /**
     * Upload gambar studio.
     */
    public function uploadImage(int $roomId, string $path, ?string $keterangan = null): RoomImage
    {
        return RoomImage::create([
            'id_room' => $roomId,
            'image_url' => $path,
            'keterangan' => $keterangan,
        ]);
    }

    /**
     * Hapus gambar studio.
     */
    public function deleteImage(int $imageId): bool
    {
        $image = RoomImage::find($imageId);
        if (!$image) return false;

        Storage::disk('public')->delete($image->image_url);
        return $image->delete();
    }
}