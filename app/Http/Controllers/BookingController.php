<?php

namespace App\Http\Controllers;

use App\Enums\BookingStatus;
use App\Enums\EditStatus;
use App\Enums\RequestSource;
use App\Http\Requests\BookingStep1Request;
use App\Http\Requests\BookingStep2Request;
use App\Services\BookingService;
use App\Services\RoomService;
use App\Models\EditRequestItem;
use App\Models\PhotoEditRequest;
use App\Utils\PhoneNormalizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(
        private BookingService $bookingService,
        private RoomService $roomService,
    ) {}

    // ==================== PUBLIC: Edit foto ====================

    public function showEditPhotoForm(int $room): View
    {
        $roomModel = $this->roomService->getActiveRoomById($room);
        abort_unless($roomModel, 404, 'Studio tidak ditemukan');

        return view('edit-foto.form', ['room' => $roomModel]);
    }

    public function storeEditPhotoRequest(Request $request, int $room): RedirectResponse
    {
        $roomModel = $this->roomService->getActiveRoomById($room);
        abort_unless($roomModel, 404, 'Studio tidak ditemukan');

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nomor_wa' => ['required', 'string', 'max:30'],
            'catatan' => ['nullable', 'string', 'max:5000'],
            'persetujuan_data' => ['accepted'],
            'foto' => ['required', 'array', 'min:1'],
            'foto.*.nama_file' => ['required', 'string', 'max:255'],
            'foto.*.instruksi_edit' => ['nullable', 'string', 'max:1000'],
        ], [
            'foto.required' => 'Tambahkan minimal satu foto untuk diedit.',
            'foto.*.nama_file.required' => 'Nama file foto wajib diisi.',
        ]);

        if (!PhoneNormalizer::isValid($data['nomor_wa'])) {
            return back()->withInput()->withErrors(['nomor_wa' => 'Nomor WhatsApp tidak valid.']);
        }

        do {
            $kode = 'EDIT-'.strtoupper(substr(bin2hex(random_bytes(5)), 0, 8));
        } while (PhotoEditRequest::where('kode_request', $kode)->exists());

        $photoRequest = PhotoEditRequest::create([
            'kode_request' => $kode,
            'id_room' => $roomModel->id_room,
            'sumber' => RequestSource::QR,
            'nama_pelanggan' => $data['nama'],
            'nomor_wa' => PhoneNormalizer::normalize($data['nomor_wa']),
            'persetujuan_data' => true,
            'persetujuan_at' => now(),
            'status_edit' => EditStatus::MENUNGGU_VERIFIKASI,
            'catatan' => $data['catatan'] ?? null,
        ]);

        foreach ($data['foto'] as $item) {
            $photoRequest->items()->create($item);
        }

        return redirect()->route('edit-foto.status', $kode)->with('success', 'Request edit foto berhasil dikirim.');
    }

    public function showEditPhotoStatus(string $kode): View
    {
        $photoRequest = PhotoEditRequest::with(['room', 'items', 'editedPhotos'])
            ->where('kode_request', strtoupper(trim($kode)))
            ->firstOrFail();

        return view('edit-foto.status', ['request' => $photoRequest]);
    }

    public function downloadEditedPhoto(string $kode, int $photo)
    {
        $photoRequest = PhotoEditRequest::where('kode_request', strtoupper(trim($kode)))->firstOrFail();
        $editedPhoto = $photoRequest->editedPhotos()->findOrFail($photo);
        $path = $editedPhoto->drive_url;

        abort_unless($path && Storage::disk('private')->exists($path), 404, 'File tidak ditemukan');

        return Storage::disk('private')->download($path, $editedPhoto->nama_file);
    }

    // ==================== PUBLIC: Wizard ====================

    /**
     * Langkah 1: form data diri, pilih studio dan paket.
     */
    public function showWizard(): View
    {
        return view('booking.wizard', [
            'rooms' => $this->roomService->getActiveRooms(),
        ]);
    }

    /**
     * Proses langkah 1: simpan ke session (PRD 7.1.1).
     */
    public function processStep1(BookingStep1Request $request): RedirectResponse
    {
        $validated = $request->validated();

        session([
            'booking_draft' => [
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'no_hp_pelanggan' => $validated['no_hp_pelanggan'],
                'email_pelanggan' => $validated['email_pelanggan'] ?? null,
                'id_room' => (int) $validated['id_room'],
                'id_package' => (int) $validated['id_package'],
            ],
        ]);

        return redirect()->route('booking.step2');
    }

    /**
     * Langkah 2: pilih slot waktu. Jika tidak ada draft, kembali ke langkah 1.
     */
    public function showStep2(): View|RedirectResponse
    {
        $draft = session('booking_draft');

        if (!$draft) {
            return redirect()->route('booking.wizard')
                ->with('error', 'Silakan mulai dari langkah pertama');
        }

        return view('booking.step2', [
            'draft' => $draft,
            'room' => $this->roomService->getActiveRoomById($draft['id_room']),
        ]);
    }

    /**
     * JSON slot kosong (PRD 8.1: GET /booking/slots).
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $roomId = $request->query('id_room');
        $packageId = $request->query('id_package');
        $date = $request->query('tanggal_sesi');

        if (!$roomId || !$packageId || !$date) {
            return response()->json(['success' => false, 'message' => 'Parameter tidak lengkap'], 400);
        }

        $slots = $this->bookingService->getAvailableSlots((int) $roomId, (int) $packageId, $date);

        return response()->json(['success' => true, 'data' => $slots]);
    }

    /**
     * Proses langkah 2: kunci slot dan buat booking (PRD 7.1.3).
     */
    public function processStep2(BookingStep2Request $request): RedirectResponse
    {
        $draft = session('booking_draft');

        if (!$draft) {
            return redirect()->route('booking.wizard')
                ->with('error', 'Sesi booking habis. Silakan mulai kembali.');
        }

        $result = $this->bookingService->lockSlotAndCreateBooking($draft, $request->validated());

        if (!$result['success']) {
            return redirect()->route('booking.step2')
                ->with('error', $result['message'])
                ->withInput();
        }

        // Hapus draft session (PRD 7.1.3)
        session()->forget('booking_draft');

        return redirect()->route('booking.payment', $result['booking']->kode_booking);
    }

    /**
     * Langkah 3: tampilkan QRIS dan total harga (PRD 7.2).
     */
    public function showPayment(string $kode): View|RedirectResponse
    {
        $booking = $this->bookingService->getBookingByCode($kode);

        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }

        if ($booking->status_booking !== BookingStatus::PENDING) {
            return redirect()->route('booking.status', $kode)
                ->with('info', 'Booking ini sudah diproses');
        }

        return view('booking.payment', [
            'booking' => $booking,
        ]);
    }

    /**
     * Langkah 4: tampil kode booking (PRD 8.1).
     */
    public function showConfirmation(string $kode): View
    {
        $booking = $this->bookingService->getBookingByCode($kode);

        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }

        return view('booking.confirmation', [
            'booking' => $booking,
        ]);
    }

    /**
     * Status booking (PRD 8.1).
     */
    public function showStatus(string $kode): View
    {
        $booking = $this->bookingService->getBookingByCode($kode);

        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }

        return view('booking.status', [
            'booking' => $booking,
        ]);
    }

    /**
     * Cek status: satu form, satu kolom kode (PRD 8.1).
     */
    public function showCheckStatus(): View
    {
        return view('check-status');
    }

    /**
     * Proses cek status: redirect ke status booking (BK-) atau edit foto (ED-).
     */
    public function processCheckStatus(Request $request): RedirectResponse
    {
        $kode = $request->validate([
            'kode' => 'required|string|max:50',
        ], [
            'kode.required' => 'Kode wajib diisi',
        ])['kode'];

        $kode = strtoupper(trim($kode));

        if (str_starts_with($kode, 'BK-')) {
            return redirect()->route('booking.status', $kode);
        }

        if (str_starts_with($kode, 'ED-')) {
            // Akan aktif di BE-3
            return redirect()->route('edit-foto.status', $kode);
        }

        return back()->with('error', 'Format kode tidak dikenali. Gunakan kode BK-xxx atau ED-xxx.');
    }

    // ==================== ADMIN: Edit foto ====================

    public function editPhotoIndex(Request $request): View
    {
        $status = $request->query('status');
        $query = \App\Models\PhotoEditRequest::with('room')->latest();
        if ($status && EditStatus::tryFrom($status)) {
            $query->where('status_edit', $status);
        }

        return view('admin.edit-foto.index', [
            'requests' => $query->get(),
            'currentStatus' => $status,
        ]);
    }

    public function editPhotoShow(int $request): View
    {
        $photoRequest = PhotoEditRequest::with(['room', 'items', 'editedPhotos', 'statusLogs.admin'])->findOrFail($request);

        return view('admin.edit-foto.show', ['request' => $photoRequest]);
    }

    public function updateEditPhotoStatus(int $request, Request $httpRequest): RedirectResponse
    {
        $photoRequest = PhotoEditRequest::findOrFail($request);
        $target = EditStatus::tryFrom($httpRequest->input('status'));
        if (!$target || !$photoRequest->status_edit->canTransitionTo($target)) {
            return back()->with('error', 'Perubahan status tidak valid.');
        }

        $old = $photoRequest->status_edit->value;
        $photoRequest->status_edit = $target;
        $photoRequest->handled_by = auth('admin')->id();
        if ($target === EditStatus::SELESAI) $photoRequest->completed_at = now();
        $photoRequest->save();
        $photoRequest->statusLogs()->create([
            'changed_by' => auth('admin')->id(),
            'status_lama' => $old,
            'status_baru' => $target->value,
            'changed_at' => now(),
        ]);

        return back()->with('success', 'Status request berhasil diperbarui.');
    }

    public function uploadEditedPhoto(int $request, Request $httpRequest): RedirectResponse
    {
        $photoRequest = PhotoEditRequest::findOrFail($request);
        $data = $httpRequest->validate([
            'foto' => ['required', 'file', 'image', 'max:10240'],
            'id_item' => ['nullable', 'integer'],
        ]);
        $file = $data['foto'];
        $path = $file->store('edited-photos', 'private');
        $photoRequest->editedPhotos()->create([
            'id_item' => $data['id_item'] ?? null,
            'nama_file' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'ukuran_file' => $file->getSize(),
            'drive_url' => $path,
            'uploaded_at' => now(),
        ]);

        return back()->with('success', 'Hasil edit berhasil diunggah.');
    }

    // ==================== ADMIN ====================

    /**
     * Daftar booking (admin).
     */
    public function adminIndex(Request $request): View
    {
        $status = $request->query('status');

        return view('admin.booking.index', [
            'bookings' => $this->bookingService->getAllBookings(status: $status),
            'currentStatus' => $status,
        ]);
    }

    /**
     * Detail booking (admin).
     */
    public function adminShow(int $booking): View
    {
        $bookingModel = \App\Models\Booking::with(['details.room', 'details.package', 'payments.verifier'])
            ->find($booking);

        if (!$bookingModel) {
            abort(404, 'Booking tidak ditemukan');
        }

        return view('admin.booking.show', [
            'booking' => $bookingModel,
        ]);
    }

    /**
     * Update status booking oleh admin (Keputusan #5).
     */
    public function updateStatus(int $booking, Request $request): RedirectResponse
    {
        $bookingModel = \App\Models\Booking::find($booking);

        if (!$bookingModel) {
            return back()->with('error', 'Booking tidak ditemukan');
        }

        $newStatus = BookingStatus::tryFrom($request->input('status'));
        if (!$newStatus) {
            return back()->with('error', 'Status tidak valid');
        }

        $result = $this->bookingService->updateBookingStatus($bookingModel, $newStatus);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}