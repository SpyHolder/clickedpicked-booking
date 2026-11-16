<?php

namespace App\Models;

use App\Enums\CabangStudio;
use App\Enums\JenisRoom;
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
        'cabang',
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

    /**
     * Label resmi cabang studio (Batu 5 atau Jalan Merdeka).
     */
    public function getCabangLabelAttribute(): string
    {
        if ($this->cabang instanceof CabangStudio) {
            return $this->cabang->label();
        }

        $cabang = (string) $this->cabang;
        if ($enum = CabangStudio::tryFrom($cabang)) {
            return $enum->label();
        }

        return str_contains(strtolower($cabang), 'merdeka') ? 'Jalan Merdeka' : 'Batu 5';
    }

    /**
     * Label resmi jenis ruangan (Photo Studio atau Photo Booth).
     */
    public function getJenisRoomLabelAttribute(): string
    {
        if ($this->jenis_room instanceof JenisRoom) {
            return $this->jenis_room->label();
        }

        $jenis = (string) $this->jenis_room;
        if ($enum = JenisRoom::tryFrom($jenis)) {
            return $enum->label();
        }

        if ($this->isPhotoBooth()) {
            return 'Photo Booth';
        }

        return 'Photo Studio';
    }

    /**
     * Nama lengkap ruangan beserta cabang dan jenis.
     */
    public function getNamaLengkapAttribute(): string
    {
        return "{$this->cabang_label} — {$this->nama_room} ({$this->jenis_room_label})";
    }

    /**
     * Apakah ruangan ini adalah Photo Booth (Walk-in only, tidak bisa di-booking online).
     */
    public function isPhotoBooth(): bool
    {
        $jenis = strtolower((string) ($this->jenis_room instanceof JenisRoom ? $this->jenis_room->value : $this->jenis_room));
        $nama = strtolower((string) $this->nama_room);

        return $jenis === 'photo_booth'
            || str_contains($jenis, 'booth')
            || str_contains($nama, 'booth');
    }

    /**
     * Sistem booking hanya berlaku untuk Room Studio (bukan Photo Booth).
     */
    public function isBookableStudio(): bool
    {
        return !$this->isPhotoBooth();
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
