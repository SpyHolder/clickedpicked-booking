<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';
    protected $primaryKey = 'id_booking';
    public $incrementing = true;

    protected $fillable = ['kode_booking', 'nama_pelanggan', 'no_hp_pelanggan', 'email_pelanggan', 'tanggal_booking', 'status_booking', 'total_harga'];

    protected function casts(): array
    {
        return ['status_booking' => BookingStatus::class, 'tanggal_booking' => 'date', 'total_harga' => 'decimal:2'];
    }

    public function details() { return $this->hasMany(BookingDetail::class, 'id_booking', 'id_booking'); }
    public function payments() { return $this->hasMany(Payment::class, 'id_booking', 'id_booking'); }
    public function photoEditRequests() { return $this->hasMany(PhotoEditRequest::class, 'id_booking', 'id_booking'); }
}