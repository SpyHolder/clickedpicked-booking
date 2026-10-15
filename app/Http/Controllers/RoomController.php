<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoomRequest;
use App\Http\Requests\PackageRequest;
use App\Services\RoomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class RoomController extends Controller
{
    public function __construct(private RoomService $roomService) {}

    /**
     * PUBLIC: Display list of active rooms for customer
     */
    public function index(): View
    {
        $rooms = $this->roomService->getAllRooms();
        
        return view('rooms.index', [
            'rooms' => $rooms
        ]);
    }

    /**
     * PUBLIC: Display single room detail with packages for customer
     */
    public function show(int $id): View
    {
        $room = $this->roomService->getRoomById($id);
        
        if (!$room) {
            abort(404, 'Ruangan tidak ditemukan');
        }
        
        $packages = $this->roomService->getRoomPackages($id);
        
        return view('rooms.show', [
            'room' => $room,
            'packages' => $packages
        ]);
    }

    /**
     * PUBLIC: Get room packages (for AJAX)
     */
    public function getPackages(int $roomId): JsonResponse
    {
        $packages = $this->roomService->getRoomPackages($roomId);
        
        return response()->json([
            'success' => true,
            'data' => $packages
        ]);
    }

    /**
     * ADMIN: Display room management page
     */
    public function adminIndex(): View
    {
        $rooms = $this->roomService->getAllRooms(includeInactive: true);
        
        return view('admin.rooms.index', [
            'rooms' => $rooms
        ]);
    }

    /**
     * ADMIN: Show create room form
     */
    public function create(): View
    {
        return view('admin.rooms.create');
    }

    /**
     * ADMIN: Store new room
     */
    public function store(RoomRequest $request): RedirectResponse
    {
        $room = $this->roomService->createRoom($request->validated());
        
        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dibuat');
    }

    /**
     * ADMIN: Show edit room form
     */
    public function edit(int $id): View
    {
        $room = $this->roomService->getRoomById($id);
        
        if (!$room) {
            abort(404, 'Ruangan tidak ditemukan');
        }
        
        return view('admin.rooms.edit', [
            'room' => $room
        ]);
    }

    /**
     * ADMIN: Update room
     */
    public function update(int $id, RoomRequest $request): RedirectResponse
    {
        $room = $this->roomService->updateRoom($id, $request->validated());
        
        if (!$room) {
            return redirect()
                ->route('admin.rooms.index')
                ->with('error', 'Ruangan tidak ditemukan');
        }
        
        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil diperbarui');
    }

    /**
     * ADMIN: Delete room (soft delete by status)
     */
    public function destroy(int $id): RedirectResponse
    {
        $deleted = $this->roomService->deleteRoom($id);
        
        if (!$deleted) {
            return redirect()
                ->route('admin.rooms.index')
                ->with('error', 'Ruangan tidak ditemukan');
        }
        
        return redirect()
            ->route('admin.rooms.index')
            ->with('success', 'Ruangan berhasil dinonaktifkan');
    }

    /**
     * ADMIN: Show room packages management
     */
    public function showPackages(int $roomId): View
    {
        $room = $this->roomService->getRoomById($roomId);
        
        if (!$room) {
            abort(404, 'Ruangan tidak ditemukan');
        }
        
        $packages = $this->roomService->getRoomPackages($roomId, includeInactive: true);
        
        return view('admin.rooms.packages', [
            'room' => $room,
            'packages' => $packages
        ]);
    }

    /**
     * ADMIN: Store new package for room
     */
    public function storePackage(int $roomId, PackageRequest $request): RedirectResponse
    {
        $package = $this->roomService->createPackage($roomId, $request->validated());
        
        return redirect()
            ->route('admin.rooms.packages', $roomId)
            ->with('success', 'Paket berhasil dibuat');
    }

    /**
     * ADMIN: Update package
     */
    public function updatePackage(int $packageId, PackageRequest $request): RedirectResponse
    {
        $package = $this->roomService->updatePackage($packageId, $request->validated());
        
        if (!$package) {
            return back()->with('error', 'Paket tidak ditemukan');
        }
        
        return back()->with('success', 'Paket berhasil diperbarui');
    }

    /**
     * ADMIN: Delete package (soft delete by is_active)
     */
    public function destroyPackage(int $packageId): RedirectResponse
    {
        $deleted = $this->roomService->deletePackage($packageId);
        
        if (!$deleted) {
            return back()->with('error', 'Paket tidak ditemukan');
        }
        
        return back()->with('success', 'Paket berhasil dinonaktifkan');
    }
}