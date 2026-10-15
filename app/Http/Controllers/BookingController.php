<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Services\BookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService) {}

    /**
     * PUBLIC: Show booking wizard - Step 1 (data diri, room, package selection)
     */
    public function showWizard(): View
    {
        return view('booking.wizard', [
            'step' => 1,
            'rooms' => $this->bookingService->getAvailableRooms()
        ]);
    }

    /**
     * PUBLIC: Process Step 1 - Save to session
     */
    public function processStep1(BookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        
        // Save to session
        session([
            'booking_draft' => [
                'nama_pelanggan' => $validated['nama_pelanggan'],
                'no_hp_pelanggan' => $validated['no_hp_pelanggan'],
                'email_pelanggan' => $validated['email_pelanggan'] ?? null,
                'id_room' => $validated['id_room'],
                'id_package' => $validated['id_package'],
            ]
        ]);
        
        return redirect()->route('booking.step2');
    }

    /**
     * PUBLIC: Show Step 2 (slot selection)
     */
    public function showStep2(): View
    {
        if (!session('booking_draft')) {
            return redirect()->route('booking.wizard')
                ->with('error', 'Silakan mulai dari langkah pertama');
        }
        
        $draft = session('booking_draft');
        
        return view('booking.step2', [
            'draft' => $draft,
            'room' => $this->bookingService->getRoomById($draft['id_room']),
            'package' => $this->bookingService->getPackageById($draft['id_package'])
        ]);
    }

    /**
     * PUBLIC: Get available slots (AJAX)
     */
    public function getAvailableSlots(Request $request): JsonResponse
    {
        $roomId = $request->input('id_room');
        $packageId = $request->input('id_package');
        $date = $request->input('tanggal_sesi');
        
        if (!$roomId || !$packageId || !$date) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak lengkap'
            ], 400);
        }
        
        $slots = $this->bookingService->getAvailableSlots(
            (int) $roomId,
            (int) $packageId,
            $date
        );
        
        return response()->json([
            'success' => true,
            'data' => $slots
        ]);
    }

    /**
     * PUBLIC: Process Step 2 - Lock slot & create booking (ATOMIC)
     */
    public function processStep2(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal_sesi' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jumlah_orang' => 'required|integer|min:1'
        ]);
        
        $draft = session('booking_draft');
        
        if (!$draft) {
            return redirect()->route('booking.wizard')
                ->with('error', 'Sesi booking habis. Silakan mulai kembali.');
        }
        
        // Attempt to lock slot and create booking (atomic operation)
        $result = $this->bookingService->lockSlotAndCreateBooking(
            $draft,
            $validated
        );
        
        if (!$result['success']) {
            // Slot was taken by someone else
            return redirect()->route('booking.step2')
                ->with('error', $result['message'])
                ->withInput();
        }
        
        // Success - clear draft session
        session()->forget('booking_draft');
        
        // Redirect to payment page
        return redirect()->route('booking.payment', $result['booking']->kode_booking);
    }

    /**
     * PUBLIC: Show Step 3 (payment - QRIS & upload proof)
     */
    public function showPayment(string $kode): View
    {
        $booking = $this->bookingService->getBookingByCode($kode);
        
        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }
        
        if ($booking->status_booking !== 'pending') {
            return redirect()->route('booking.status', $kode)
                ->with('info', 'Booking ini sudah diproses');
        }
        
        return view('booking.payment', [
            'booking' => $booking,
            'qris_image' => config('app.qris_image_url') // or from settings
        ]);
    }

    /**
     * PUBLIC: Show Step 4 (confirmation - display kode_booking)
     */
    public function showConfirmation(string $kode): View
    {
        $booking = $this->bookingService->getBookingByCode($kode);
        
        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }
        
        return view('booking.confirmation', [
            'booking' => $booking
        ]);
    }

    /**
     * PUBLIC: Show booking status
     */
    public function showStatus(string $kode): View
    {
        $booking = $this->bookingService->getBookingByCode($kode);
        
        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }
        
        return view('booking.status', [
            'booking' => $booking
        ]);
    }

    /**
     * PUBLIC: Check availability (AJAX)
     */
    public function checkAvailability(Request $request): JsonResponse
    {
        $roomId = $request->input('id_room');
        $date = $request->input('tanggal_sesi');
        $jamMulai = $request->input('jam_mulai');
        $jamSelesai = $request->input('jam_selesai');
        
        if (!$roomId || !$date || !$jamMulai || !$jamSelesai) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak lengkap'
            ], 400);
        }
        
        $available = $this->bookingService->checkSlotAvailability(
            (int) $roomId,
            $date,
            $jamMulai,
            $jamSelesai
        );
        
        return response()->json([
            'success' => true,
            'available' => $available
        ]);
    }

    /**
     * ADMIN: Show all bookings list with filters
     */
    public function adminIndex(Request $request): View
    {
        $status = $request->query('status');
        $bookings = $this->bookingService->getAllBookings(status: $status);
        
        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'currentStatus' => $status
        ]);
    }

    /**
     * ADMIN: Show booking detail
     */
    public function adminShow(string $kode): View
    {
        $booking = $this->bookingService->getBookingByCode($kode);
        
        if (!$booking) {
            abort(404, 'Booking tidak ditemukan');
        }
        
        return view('admin.bookings.show', [
            'booking' => $booking
        ]);
    }

    /**
     * ADMIN: Confirm booking (after payment verified)
     */
    public function confirm(string $kode): RedirectResponse
    {
        $booking = $this->bookingService->confirmBooking($kode);
        
        if (!$booking) {
            return back()->with('error', 'Booking tidak ditemukan');
        }
        
        return back()->with('success', 'Booking berhasil dikonfirmasi');
    }

    /**
     * ADMIN: Cancel booking
     */
    public function cancel(string $kode): RedirectResponse
    {
        $booking = $this->bookingService->cancelBooking($kode);
        
        if (!$booking) {
            return back()->with('error', 'Booking tidak ditemukan');
        }
        
        return back()->with('success', 'Booking berhasil dibatalkan');
    }

    /**
     * ADMIN: Complete booking (mark as done)
     */
    public function complete(string $kode): RedirectResponse
    {
        $booking = $this->bookingService->completeBooking($kode);
        
        if (!$booking) {
            return back()->with('error', 'Booking tidak ditemukan');
        }
        
        return back()->with('success', 'Booking berhasil diselesaikan');
    }
}