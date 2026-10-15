<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EditedPhoto extends Model
{
    protected $table='edited_photos';
    protected $primaryKey='id_photo';
    public $timestamps=false;
    protected $fillable=['id_request','id_item','nama_file','drive_file_id','drive_url','mime_type','ukuran_file','uploaded_at'];
    protected function casts(){return['uploaded_at'=>'datetime'];}
    public function request(){return $this->belongsTo(PhotoEditRequest::class,'id_request','id_request');}
    public function item(){return $this->belongsTo(EditRequestItem::class,'id_item','id_item');}
    public function deliveryItems(){return $this->hasMany(DeliveryItem::class,'id_photo','id_photo');}
}