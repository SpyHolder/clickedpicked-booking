<?php

namespace App\Models;

use App\Enums\RoomStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use HasFactory;

    protected $table = 'rooms';
    protected $primaryKey = 'id_room';

    protected $fillable = [
        'nama_room',
        'jenis_room',
        'deskripsi',
        'kapasitas',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'status' => RoomStatus::class,
        ];
    }

    public function images()
    {
        return $this->hasMany(RoomImage::class, 'id_room', 'id_room');
    }

    public function packages()
    {
        return $this->hasMany(RoomPackage::class, 'id_room', 'id_room');
    }

    public function bookingDetails()
    {
        return $this->hasMany(BookingDetail::class, 'id_room', 'id_room');
    }

    public function photoEditRequests()
    {
        return $this->hasMany(PhotoEditRequest::class, 'id_room', 'id_room');
    }
}