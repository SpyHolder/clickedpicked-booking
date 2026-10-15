<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BookingDetail extends Model
{
    use HasFactory;
    protected $table = 'booking_details';
    protected $primaryKey = 'id_detail';
    protected $fillable = ['id_booking','id_room','id_package','tanggal_sesi','jam_mulai','jam_selesai','jumlah_orang','harga'];
    protected function casts() { return ['tanggal_sesi'=>'date','harga'=>'decimal:2']; }
    public function booking() { return $this->belongsTo(Booking::class, 'id_booking', 'id_booking'); }
    public function room() { return $this->belongsTo(Room::class, 'id_room', 'id_room'); }
    public function package() { return $this->belongsTo(RoomPackage::class, 'id_package', 'id_package'); }
    public function photoEditRequests() { return $this->hasMany(PhotoEditRequest::class, 'id_detail', 'id_detail'); }
}