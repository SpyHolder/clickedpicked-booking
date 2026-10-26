<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\Booking;
use App\Enums\PaymentStatus;
use App\Enums\BookingStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentService
{
    /**
     * Buat payment saat pelanggan unggah bukti bayar (PRD 7.2).
     * Keputusan #4: hanya unggah bukti, nominal dari total_harga, tanggal = now().
     */
    public function createPaymentForBooking(Booking $booking, string $proofPath): Payment
    {
        return Payment::create([
            'id_booking' => $booking->id_booking,
            'jenis_pembayaran' => 'booking',
            'metode_pembayaran' => 'qris',
            'jumlah_bayar' => $booking->total_harga,
            'tanggal_bayar' => now()->toDateString(),
            'status_pembayaran' => PaymentStatus::PENDING->value,
            'bukti_pembayaran' => $proofPath,
        ]);
    }

    /**
     * Verifikasi pembayaran oleh admin (PRD 7.2).
     * Payment → paid, booking → confirmed, verified_by terisi.
     */
    public function verifyPayment(int $paymentId, int $adminId): array
    {
        return DB::transaction(function () use ($paymentId, $adminId) {
            $payment = Payment::find($paymentId);
            if (!$payment) {
                return ['success' => false, 'message' => 'Pembayaran tidak ditemukan'];
            }

            if ($payment->status_pembayaran !== PaymentStatus::PENDING) {
                return ['success' => false, 'message' => 'Pembayaran ini sudah diproses'];
            }

            $payment->update([
                'status_pembayaran' => PaymentStatus::PAID->value,
                'verified_by' => $adminId,
            ]);

            // Otomatis konfirmasi booking
            if ($payment->id_booking) {
                $booking = $payment->booking;
                if ($booking && $booking->status_booking === BookingStatus::PENDING) {
                    $booking->update(['status_booking' => BookingStatus::CONFIRMED->value]);
                }
            }

            return ['success' => true, 'message' => 'Pembayaran berhasil diverifikasi', 'payment' => $payment];
        });
    }

    /**
     * Tolak pembayaran (Keputusan #2):
     * Payment lama → failed, pelanggan unggah ulang membuat payment baru.
     */
    public function rejectPayment(int $paymentId, int $adminId): array
    {
        $payment = Payment::find($paymentId);
        if (!$payment) {
            return ['success' => false, 'message' => 'Pembayaran tidak ditemukan'];
        }

        if ($payment->status_pembayaran !== PaymentStatus::PENDING) {
            return ['success' => false, 'message' => 'Pembayaran ini sudah diproses'];
        }

        $payment->update([
            'status_pembayaran' => PaymentStatus::FAILED->value,
            'verified_by' => $adminId,
        ]);

        return ['success' => true, 'message' => 'Pembayaran ditolak', 'payment' => $payment];
    }

    /**
     * Unggah ulang bukti bayar (Keputusan #2): buat payment baru.
     */
    public function reuploadProof(Booking $booking, string $proofPath): Payment
    {
        return $this->createPaymentForBooking($booking, $proofPath);
    }

    /**
     * Ambil payment by ID.
     */
    public function getPaymentById(int $id): ?Payment
    {
        return Payment::with(['booking', 'verifier'])->find($id);
    }

    /**
     * Daftar payment pending untuk verifikasi admin (PRD 9).
     */
    public function getPendingPayments(int $perPage = 15): LengthAwarePaginator
    {
        return Payment::where('status_pembayaran', PaymentStatus::PENDING->value)
            ->with(['booking'])
            ->orderBy('created_at', 'asc')
            ->paginate($perPage);
    }

    /**
     * Tampilkan bukti pembayaran dari disk privat (PRD 7.2).
     */
    public function getProofPath(Payment $payment): ?string
    {
        if (!$payment->bukti_pembayaran) return null;

        $fullPath = Storage::disk('private')->path($payment->bukti_pembayaran);
        return file_exists($fullPath) ? $fullPath : null;
    }
}