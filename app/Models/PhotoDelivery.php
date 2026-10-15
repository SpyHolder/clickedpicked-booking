<?php
namespace App\Models;
use App\Enums\DeliveryStatus;
use Illuminate\Database\Eloquent\Model;
class PhotoDelivery extends Model
{
    protected $table='photo_deliveries';
    protected $primaryKey='id_delivery';
    protected $fillable=['id_request','id_admin','metode','recipient_phone','pesan','status_pengiriman','external_message_id','retry_count','error_message','response_api','sent_at','delivered_at','read_at'];
    protected function casts(){return['status_pengiriman'=>DeliveryStatus::class,'sent_at'=>'datetime','delivered_at'=>'datetime','read_at'=>'datetime'];}
    public function request(){return $this->belongsTo(PhotoEditRequest::class,'id_request','id_request');}
    public function admin(){return $this->belongsTo(Admin::class,'id_admin','id_admin');}
    public function items(){return $this->hasMany(DeliveryItem::class,'id_delivery','id_delivery');}
}