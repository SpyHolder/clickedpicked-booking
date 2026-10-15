<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomImage extends Model
{
    use HasFactory;
    protected $table = 'room_images';
    protected $primaryKey = 'id_image';
    protected $fillable = ['id_room', 'image_url', 'keterangan'];
    public function room() { return $this->belongsTo(Room::class, 'id_room', 'id_room'); }
}