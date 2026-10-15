<?php
namespace App\Services;
use App\Models\Payment;
use App\Models\Booking;
use App\Models\PhotoEditRequest;
use App\Enums\PaymentStatus;
use Illuminate\Support\Facades\Storage;
class PaymentService
{
    public function createPaymentForBooking(string $bookingCode,array $data):?Payment
    {
        $booking=Booking::where('kode_booking',$bookingCode)->first();
        if(!$booking)return null;
        return Payment::create([
            'id_booking'=>$booking->id_booking,
            'jenis_pembayaran'=>'booking',
            'metode_pembayaran'=>$data['metode_pembayaran'],
            'jumlah_bayar'=>$data['jumlah_bayar'],
            'tanggal_bayar'=>now(),
            'status_pembayaran'=>PaymentStatus::Pending,
            'bukti_pembayaran'=>$data['bukti_pembayaran']??null,
        ]);
    }
    public function createPaymentForEditRequest(int $requestId,array $data):?Payment
    {
        $request=PhotoEditRequest::find($requestId);
        if(!$request)return null;
        return Payment::create([
            'id_request'=>$requestId,
            'jenis_pembayaran'=>'edit',
            'metode_pembayaran'=>$data['metode_pembayaran'],
            'jumlah_bayar'=>$data['jumlah_bayar'],
            'tanggal_bayar'=>now(),
            'status_pembayaran'=>PaymentStatus::Pending,
            'bukti_pembayaran'=>$data['bukti_pembayaran']??null,
        ]);
    }
    public function verifyPayment(int $paymentId,int $adminId):?Payment
    {
        $payment=Payment::find($paymentId);
        if(!$payment)return null;
        $payment->update([
            'status_pembayaran'=>PaymentStatus::Verified,
            'verified_by'=>$adminId,
        ]);
        if($payment->id_booking){
            $booking=$payment->booking;
            $booking->update(['status_booking'=>\App\Enums\BookingStatus::Confirmed]);
        }
        return $payment;
    }
    public function rejectPayment(int $paymentId,int $adminId):?Payment
    {
        $payment=Payment::find($paymentId);
        if(!$payment)return null;
        $payment->update([
            'status_pembayaran'=>PaymentStatus::Rejected,
            'verified_by'=>$adminId,
        ]);
        return $payment;
    }
    public function uploadProof(int $paymentId,string $filePath):?Payment
    {
        $payment=Payment::find($paymentId);
        if(!$payment)return null;
        if($payment->bukti_pembayaran){
            Storage::disk('public')->delete($payment->bukti_pembayaran);
        }
        $payment->update(['bukti_pembayaran'=>$filePath]);
        return $payment;
    }
}