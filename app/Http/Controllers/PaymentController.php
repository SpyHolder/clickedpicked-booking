<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentProofRequest;
use App\Services\BookingService;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(
        private PaymentService $paymentService,
        private BookingService $bookingService,
    ) {}

    /**
     * PUBLIC: Upload bukti bayar (step 3 wizard) — Keputusan #4.
     */
    public function uploadProof(string $kode, PaymentProofRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->getBookingByCode($kode);

        if (!$booking) {
            return back()->with('error', 'Booking tidak ditemukan');
        }

        $path = $request->file('bukti_pembayaran')->store('payment-proofs', 'private');
        $this->paymentService->createPaymentForBooking($booking, $path);

        return redirect()->route('booking.done', $kode)
            ->with('success', 'Bukti pembayaran berhasil diupload. Silakan tunggu verifikasi admin.');
    }

    /**
     * PUBLIC: Upload ulang bukti bayar (Keputusan #2: buat payment baru).
     */
    public function reuploadProof(string $kode, PaymentProofRequest $request): RedirectResponse
    {
        $booking = $this->bookingService->getBookingByCode($kode);

        if (!$booking) {
            return back()->with('error', 'Booking tidak ditemukan');
        }

        $path = $request->file('bukti_pembayaran')->store('payment-proofs', 'private');
        $this->paymentService->reuploadProof($booking, $path);

        return back()->with('success', 'Bukti pembayaran baru berhasil diupload. Silakan tunggu verifikasi admin.');
    }

    // ==================== ADMIN ====================

    /**
     * Tampilkan bukti bayar dari disk privat (PRD 7.2).
     */
    public function showProof(int $payment): Response
    {
        $paymentModel = $this->paymentService->getPaymentById($payment);
        $fullPath = $paymentModel ? $this->paymentService->getProofPath($paymentModel) : null;

        if (!$fullPath) {
            abort(404, 'Bukti pembayaran tidak ditemukan');
        }

        return response()->file($fullPath, [
            'Content-Type' => mime_content_type($fullPath),
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Verifikasi pembayaran (PRD 7.2).
     */
    public function verify(int $payment): RedirectResponse
    {
        $adminId = auth('admin')->id();
        $result = $this->paymentService->verifyPayment($payment, $adminId);

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}