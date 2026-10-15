<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class RoomPackage extends Model
{
    use HasFactory;
    protected $table = 'room_packages';
    protected $primaryKey = 'id_package';
    protected $fillable = ['id_room','harga','tipe_harga','durasi_menit','deskripsi_benefit','is_active'];
    protected function casts() { return ['is_active'=>'boolean','harga'=>'decimal:2']; }
    public function room() { return $this->belongsTo(Room::class, 'id_room', 'id_room'); }
    public function bookingDetails() { return $this->hasMany(BookingDetail::class, 'id_package', 'id_package'); }
}