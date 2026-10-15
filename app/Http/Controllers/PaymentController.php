<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    /**
     * PUBLIC: Upload payment proof for booking (Step 3 of wizard)
     * This is part of BookingController flow, but separated for clarity
     */
    public function uploadProofForBooking(string $bookingCode, PaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        if (!$request->hasFile('bukti_pembayaran')) {
            return back()->with('error', 'File bukti pembayaran wajib diupload');
        }
        
        // Store in private disk (not public) for security
        $path = $request->file('bukti_pembayaran')->store('payment-proofs', 'private');
        $data['bukti_pembayaran'] = $path;
        
        $payment = $this->paymentService->createPaymentForBooking($bookingCode, $data);
        
        if (!$payment) {
            return back()->with('error', 'Booking tidak ditemukan');
        }
        
        // Redirect to confirmation page (Step 4)
        return redirect()
            ->route('booking.confirmation', $bookingCode)
            ->with('success', 'Bukti pembayaran berhasil diupload. Silakan tunggu verifikasi admin.');
    }

    /**
     * PUBLIC: Upload payment proof for edit request (if edit is paid service)
     * Decision pending in PRD section 4.2
     */
    public function uploadProofForEditRequest(int $requestId, PaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        
        if (!$request->hasFile('bukti_pembayaran')) {
            return back()->with('error', 'File bukti pembayaran wajib diupload');
        }
        
        $path = $request->file('bukti_pembayaran')->store('payment-proofs', 'private');
        $data['bukti_pembayaran'] = $path;
        
        $payment = $this->paymentService->createPaymentForEditRequest($requestId, $data);
        
        if (!$payment) {
            return back()->with('error', 'Request edit tidak ditemukan');
        }
        
        return back()->with('success', 'Bukti pembayaran berhasil diupload');
    }

    /**
     * ADMIN: Display payment proof image from private storage
     */
    public function showProof(int $paymentId): Response
    {
        $payment = $this->paymentService->getPaymentById($paymentId);
        
        if (!$payment || !$payment->bukti_pembayaran) {
            abort(404, 'Bukti pembayaran tidak ditemukan');
        }
        
        // Security check: Only admin can view
        if (!auth('admin')->check()) {
            abort(403, 'Unauthorized');
        }
        
        $filePath = storage_path('app/private/' . $payment->bukti_pembayaran);
        
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan');
        }
        
        $mimeType = mime_content_type($filePath);
        
        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline'
        ]);
    }

    /**
     * ADMIN: Show payment verification page
     */
    public function showVerification(int $paymentId): View
    {
        $payment = $this->paymentService->getPaymentById($paymentId);
        
        if (!$payment) {
            abort(404, 'Pembayaran tidak ditemukan');
        }
        
        return view('admin.payments.verify', [
            'payment' => $payment,
            'booking' => $payment->booking,
            'editRequest' => $payment->editRequest
        ]);
    }

    /**
     * ADMIN: Verify payment (approve)
     * This will also update booking status to 'confirmed'
     */
    public function verify(int $paymentId): RedirectResponse
    {
        $adminId = auth('admin')->id();
        
        $payment = $this->paymentService->verifyPayment($paymentId, $adminId);
        
        if (!$payment) {
            return back()->with('error', 'Pembayaran tidak ditemukan');
        }
        
        return back()->with('success', 'Pembayaran berhasil diverifikasi. Booking telah dikonfirmasi.');
    }

    /**
     * ADMIN: Reject payment
     * Behavior depends on decision in PRD section 4.2:
     * - Customer uploads again, OR
     * - Booking is cancelled
     */
    public function reject(int $paymentId, Request $request): RedirectResponse
    {
        $request->validate([
            'alasan_penolakan' => 'required|string|max:500'
        ]);
        
        $adminId = auth('admin')->id();
        $reason = $request->input('alasan_penolakan');
        
        $payment = $this->paymentService->rejectPayment($paymentId, $adminId, $reason);
        
        if (!$payment) {
            return back()->with('error', 'Pembayaran tidak ditemukan');
        }
        
        return back()->with('success', 'Pembayaran berhasil ditolak. Pelanggan akan dinotifikasi.');
    }

    /**
     * ADMIN: Show all payments needing verification
     */
    public function pendingVerifications(): View
    {
        $payments = $this->paymentService->getPendingPayments();
        
        return view('admin.payments.pending', [
            'payments' => $payments
        ]);
    }

    /**
     * PUBLIC: Re-upload payment proof (if rejected)
     * This allows customer to upload again without creating new booking
     */
    public function reuploadProof(string $bookingCode, PaymentRequest $request): RedirectResponse
    {
        if (!$request->hasFile('bukti_pembayaran')) {
            return back()->with('error', 'File bukti pembayaran wajib diupload');
        }
        
        // Get existing payment
        $payment = $this->paymentService->getPaymentByBookingCode($bookingCode);
        
        if (!$payment) {
            return back()->with('error', 'Pembayaran tidak ditemukan');
        }
        
        // Delete old proof if exists
        if ($payment->bukti_pembayaran) {
            \Storage::disk('private')->delete($payment->bukti_pembayaran);
        }
        
        // Upload new proof
        $path = $request->file('bukti_pembayaran')->store('payment-proofs', 'private');
        
        $updated = $this->paymentService->updatePaymentProof($payment->id_payment, $path);
        
        if (!$updated) {
            return back()->with('error', 'Gagal mengupload bukti pembayaran');
        }
        
        return back()->with('success', 'Bukti pembayaran baru berhasil diupload. Silakan tunggu verifikasi admin.');
    }
}