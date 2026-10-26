<?php

namespace App\Http\Controllers;

use App\Http\Requests\PackageRequest;
use App\Http\Requests\RoomRequest;
use App\Services\RoomService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoomController extends Controller
{
    public function __construct(private RoomService $roomService) {}

    // ==================== PUBLIC ====================

    /**
     * Daftar studio aktif untuk pelanggan.
     */
    public function index(): View
    {
        return view('studio.index', [
            'rooms' => $this->roomService->getActiveRooms(),
        ]);
    }

    /**
     * Detail studio aktif dengan paket.
     */
    public function show(int $room): View
    {
        $roomModel = $this->roomService->getActiveRoomById($room);

        if (!$roomModel) {
            abort(404, 'Studio tidak ditemukan');
        }

        return view('studio.show', [
            'room' => $roomModel,
        ]);
    }

    // ==================== ADMIN ====================

    /**
     * Daftar semua studio (admin, termasuk nonaktif).
     */
    public function adminIndex(): View
    {
        return view('admin.studio.index', [
            'rooms' => $this->roomService->getAllRoomsAdmin(),
        ]);
    }

    /**
     * Form buat studio baru.
     */
    public function create(): View
    {
        return view('admin.studio.create');
    }

    /**
     * Simpan studio baru.
     */
    public function store(RoomRequest $request): RedirectResponse
    {
        $this->roomService->createRoom($request->validated());

        return redirect()->route('admin.studio.index')
            ->with('success', 'Studio berhasil dibuat');
    }

    /**
     * Form edit studio.
     */
    public function edit(int $room): View
    {
        $roomModel = $this->roomService->getRoomById($room);

        if (!$roomModel) {
            abort(404, 'Studio tidak ditemukan');
        }

        return view('admin.studio.edit', [
            'room' => $roomModel,
        ]);
    }

    /**
     * Update studio.
     */
    public function update(int $room, RoomRequest $request): RedirectResponse
    {
        $roomModel = $this->roomService->updateRoom($room, $request->validated());

        if (!$roomModel) {
            return redirect()->route('admin.studio.index')
                ->with('error', 'Studio tidak ditemukan');
        }

        return redirect()->route('admin.studio.index')
            ->with('success', 'Studio berhasil diperbarui');
    }

    /**
     * Toggle status studio (aktif ↔ nonaktif). PRD: tanpa hapus.
     */
    public function toggleStatus(int $room): RedirectResponse
    {
        $roomModel = $this->roomService->toggleStatus($room);

        if (!$roomModel) {
            return back()->with('error', 'Studio tidak ditemukan');
        }

        $label = $roomModel->status->label();
        return back()->with('success', "Status studio diubah menjadi {$label}");
    }

    // ── Paket ──

    /**
     * Daftar paket per studio (admin).
     */
    public function showPackages(int $room): View
    {
        $roomModel = $this->roomService->getRoomById($room);

        if (!$roomModel) {
            abort(404, 'Studio tidak ditemukan');
        }

        return view('admin.studio.packages', [
            'room' => $roomModel,
            'packages' => $this->roomService->getAllPackages($room),
        ]);
    }

    /**
     * Buat paket baru.
     */
    public function storePackage(int $room, PackageRequest $request): RedirectResponse
    {
        $this->roomService->createPackage($room, $request->validated());

        return redirect()->route('admin.studio.packages', $room)
            ->with('success', 'Paket berhasil dibuat');
    }

    /**
     * Update paket (termasuk toggle is_active).
     */
    public function updatePackage(int $room, int $package, PackageRequest $request): RedirectResponse
    {
        $result = $this->roomService->updatePackage($package, $request->validated());

        if (!$result) {
            return back()->with('error', 'Paket tidak ditemukan');
        }

        return back()->with('success', 'Paket berhasil diperbarui');
    }

    // ── Gambar ──

    /**
     * Upload gambar studio.
     */
    public function uploadImage(int $room, Request $request): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'keterangan' => 'nullable|string|max:100',
        ], [
            'image.required' => 'Gambar wajib dipilih',
            'image.image' => 'File harus berupa gambar',
            'image.max' => 'Ukuran gambar maksimal 5MB',
        ]);

        $path = $request->file('image')->store("studio/{$room}", 'public');
        $this->roomService->uploadImage($room, $path, $request->input('keterangan'));

        return back()->with('success', 'Gambar berhasil diupload');
    }

    /**
     * Hapus gambar studio.
     */
    public function deleteImage(int $room, int $image): RedirectResponse
    {
        $deleted = $this->roomService->deleteImage($image);

        if (!$deleted) {
            return back()->with('error', 'Gambar tidak ditemukan');
        }

        return back()->with('success', 'Gambar berhasil dihapus');
    }

    /**
     * QR code halaman edit foto studio (PRD 8.2 — stub tampilan).
     */
    public function showQr(int $room): View
    {
        $roomModel = $this->roomService->getRoomById($room);

        if (!$roomModel) {
            abort(404, 'Studio tidak ditemukan');
        }

        return view('admin.studio.qr', [
            'room' => $roomModel,
        ]);
    }
}