<?php
namespace App\Services;
use App\Models\Booking;
use App\Models\BookingDetail;
use App\Enums\BookingStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class BookingService
{
    public function getAllBookings(int $perPage=15,?string $status=null)
    {
        $query=Booking::with(['room','details']);
        if($status){
            $query->where('status_booking',$status);
        }
        return $query->orderBy('created_at','desc')->paginate($perPage);
    }
    public function getBookingByCode(string $code):?Booking
    {
        return Booking::with(['room','details','payments'])
            ->where('kode_booking',$code)
            ->first();
    }
    public function createBooking(array $data):Booking
    {
        return DB::transaction(function()use($data){
            $booking=Booking::create([
                'kode_booking'=>$this->generateBookingCode(),
                'id_room'=>$data['id_room'],
                'nama_pelanggan'=>$data['nama_pelanggan'],
                'nomor_wa'=>$data['nomor_wa'],
                'email'=>$data['email']??null,
                'tanggal_mulai'=>$data['tanggal_mulai'],
                'tanggal_selesai'=>$data['tanggal_selesai'],
                'total_harga'=>$data['total_harga'],
                'status_booking'=>BookingStatus::Pending,
            ]);
            if(isset($data['details'])&&is_array($data['details'])){
                foreach($data['details']as $detail){
                    $booking->details()->create([
                        'id_package'=>$detail['id_package'],
                        'tanggal_sesi'=>$detail['tanggal_sesi'],
                        'jam_mulai'=>$detail['jam_mulai'],
                        'jam_selesai'=>$detail['jam_selesai'],
                        'harga'=>$detail['harga'],
                    ]);
                }
            }
            return $booking->load('details');
        });
    }
    public function updateBookingStatus(string $code,BookingStatus $status):?Booking
    {
        $booking=Booking::where('kode_booking',$code)->first();
        if(!$booking)return null;
        $booking->update(['status_booking'=>$status]);
        return $booking;
    }
    public function cancelBooking(string $code):?Booking
    {
        return $this->updateBookingStatus($code,BookingStatus::Cancelled);
    }
    public function confirmBooking(string $code):?Booking
    {
        return $this->updateBookingStatus($code,BookingStatus::Confirmed);
    }
    public function completeBooking(string $code):?Booking
    {
        return $this->updateBookingStatus($code,BookingStatus::Completed);
    }
    public function checkSlotAvailability(int $roomId,string $date,string $jamMulai,string $jamSelesai):bool
    {
        $conflicts=BookingDetail::whereHas('booking',function($q)use($roomId){
            $q->where('id_room',$roomId)
              ->whereIn('status_booking',[BookingStatus::Pending,BookingStatus::Confirmed]);
        })
        ->where('tanggal_sesi',$date)
        ->where(function($q)use($jamMulai,$jamSelesai){
            $q->whereBetween('jam_mulai',[$jamMulai,$jamSelesai])
              ->orWhereBetween('jam_selesai',[$jamMulai,$jamSelesai])
              ->orWhere(function($q2)use($jamMulai,$jamSelesai){
                  $q2->where('jam_mulai','<=',$jamMulai)
                     ->where('jam_selesai','>=',$jamSelesai);
              });
        })
        ->exists();
        return !$conflicts;
    }
    private function generateBookingCode():string
    {
        do{
            $code='BK'.Carbon::now()->format('Ymd').strtoupper(bin2hex(random_bytes(3)));
        }while(Booking::where('kode_booking',$code)->exists());
        return $code;
    }
}