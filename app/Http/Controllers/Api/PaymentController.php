<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentRequest;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
class PaymentController extends Controller
{
    public function __construct(private PaymentService $paymentService){}
    public function createForBooking(string $bookingCode,PaymentRequest $request):JsonResponse
    {
        $data=$request->validated();
        if($request->hasFile('bukti_pembayaran')){
            $path=$request->file('bukti_pembayaran')->store('payments','public');
            $data['bukti_pembayaran']=$path;
        }
        $payment=$this->paymentService->createPaymentForBooking($bookingCode,$data);
        if(!$payment){
            return response()->json([
                'success'=>false,
                'message'=>'Booking tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Pembayaran berhasil dicatat',
            'data'=>$payment
        ],201);
    }
    public function createForEditRequest(int $requestId,PaymentRequest $request):JsonResponse
    {
        $data=$request->validated();
        if($request->hasFile('bukti_pembayaran')){
            $path=$request->file('bukti_pembayaran')->store('payments','public');
            $data['bukti_pembayaran']=$path;
        }
        $payment=$this->paymentService->createPaymentForEditRequest($requestId,$data);
        if(!$payment){
            return response()->json([
                'success'=>false,
                'message'=>'Request edit tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Pembayaran berhasil dicatat',
            'data'=>$payment
        ],201);
    }
    public function verify(int $paymentId):JsonResponse
    {
        $adminId=auth('admin')->id();
        $payment=$this->paymentService->verifyPayment($paymentId,$adminId);
        if(!$payment){
            return response()->json([
                'success'=>false,
                'message'=>'Pembayaran tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Pembayaran berhasil diverifikasi',
            'data'=>$payment
        ]);
    }
    public function reject(int $paymentId):JsonResponse
    {
        $adminId=auth('admin')->id();
        $payment=$this->paymentService->rejectPayment($paymentId,$adminId);
        if(!$payment){
            return response()->json([
                'success'=>false,
                'message'=>'Pembayaran tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Pembayaran berhasil ditolak',
            'data'=>$payment
        ]);
    }
    public function uploadProof(int $paymentId,PaymentRequest $request):JsonResponse
    {
        if(!$request->hasFile('bukti_pembayaran')){
            return response()->json([
                'success'=>false,
                'message'=>'File bukti pembayaran wajib diupload'
            ],400);
        }
        $path=$request->file('bukti_pembayaran')->store('payments','public');
        $payment=$this->paymentService->uploadProof($paymentId,$path);
        if(!$payment){
            return response()->json([
                'success'=>false,
                'message'=>'Pembayaran tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Bukti pembayaran berhasil diupload',
            'data'=>$payment
        ]);
    }
}