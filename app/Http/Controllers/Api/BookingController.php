<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Http\Requests\BookingRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
class BookingController extends Controller
{
    public function __construct(private BookingService $bookingService){}
    public function index():JsonResponse
    {
        $status=request()->query('status');
        $bookings=$this->bookingService->getAllBookings(status:$status);
        return response()->json([
            'success'=>true,
            'data'=>$bookings->items(),
            'pagination'=>[
                'total'=>$bookings->total(),
                'per_page'=>$bookings->perPage(),
                'current_page'=>$bookings->currentPage(),
                'last_page'=>$bookings->lastPage(),
            ]
        ]);
    }
    public function show(string $code):JsonResponse
    {
        $booking=$this->bookingService->getBookingByCode($code);
        if(!$booking){
            return response()->json([
                'success'=>false,
                'message'=>'Booking tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'data'=>$booking
        ]);
    }
    public function store(BookingRequest $request):JsonResponse
    {
        $booking=$this->bookingService->createBooking($request->validated());
        return response()->json([
            'success'=>true,
            'message'=>'Booking berhasil dibuat',
            'data'=>$booking
        ],201);
    }
    public function checkAvailability():JsonResponse
    {
        $roomId=request()->input('id_room');
        $date=request()->input('tanggal_sesi');
        $jamMulai=request()->input('jam_mulai');
        $jamSelesai=request()->input('jam_selesai');
        if(!$roomId||!$date||!$jamMulai||!$jamSelesai){
            return response()->json([
                'success'=>false,
                'message'=>'Parameter tidak lengkap'
            ],400);
        }
        $available=$this->bookingService->checkSlotAvailability(
            (int)$roomId,
            $date,
            $jamMulai,
            $jamSelesai
        );
        return response()->json([
            'success'=>true,
            'available'=>$available
        ]);
    }
    public function confirm(string $code):JsonResponse
    {
        $booking=$this->bookingService->confirmBooking($code);
        if(!$booking){
            return response()->json([
                'success'=>false,
                'message'=>'Booking tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Booking berhasil dikonfirmasi',
            'data'=>$booking
        ]);
    }
    public function cancel(string $code):JsonResponse
    {
        $booking=$this->bookingService->cancelBooking($code);
        if(!$booking){
            return response()->json([
                'success'=>false,
                'message'=>'Booking tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Booking berhasil dibatalkan',
            'data'=>$booking
        ]);
    }
    public function complete(string $code):JsonResponse
    {
        $booking=$this->bookingService->completeBooking($code);
        if(!$booking){
            return response()->json([
                'success'=>false,
                'message'=>'Booking tidak ditemukan'
            ],404);
        }
        return response()->json([
            'success'=>true,
            'message'=>'Booking berhasil diselesaikan',
            'data'=>$booking
        ]);
    }
}