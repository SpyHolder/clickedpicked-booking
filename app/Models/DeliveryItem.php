<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DeliveryItem extends Model
{
    protected $table='delivery_items';
    protected $primaryKey='id_delivery_item';
    public $timestamps=false;
    protected $fillable=['id_delivery','id_photo'];
    public function delivery(){return $this->belongsTo(PhotoDelivery::class,'id_delivery','id_delivery');}
    public function photo(){return $this->belongsTo(EditedPhoto::class,'id_photo','id_photo');}
}