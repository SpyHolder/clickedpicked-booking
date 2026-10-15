<?php
namespace App\Models;
use App\Enums\EditStatus;
use App\Enums\RequestSource;
use Illuminate\Database\Eloquent\Model;
class PhotoEditRequest extends Model
{
    protected $table='photo_edit_requests';
    protected $primaryKey='id_request';
    protected $fillable=['kode_request','id_room','id_booking','id_detail','sumber','created_by','verified_by','handled_by','nama_pelanggan','nomor_wa','perlu_dicek','persetujuan_data','persetujuan_at','status_edit','alasan_penolakan','drive_folder_id','drive_folder_url','catatan','deadline','verified_at','completed_at'];
    protected function casts(){return['status_edit'=>EditStatus::class,'sumber'=>RequestSource::class,'perlu_dicek'=>'boolean','persetujuan_data'=>'boolean','persetujuan_at'=>'datetime','deadline'=>'datetime','verified_at'=>'datetime','completed_at'=>'datetime'];}
    public function room(){return $this->belongsTo(Room::class,'id_room','id_room');}
    public function booking(){return $this->belongsTo(Booking::class,'id_booking','id_booking');}
    public function detail(){return $this->belongsTo(BookingDetail::class,'id_detail','id_detail');}
    public function creator(){return $this->belongsTo(Admin::class,'created_by','id_admin');}
    public function verifier(){return $this->belongsTo(Admin::class,'verified_by','id_admin');}
    public function handler(){return $this->belongsTo(Admin::class,'handled_by','id_admin');}
    public function items(){return $this->hasMany(EditRequestItem::class,'id_request','id_request');}
    public function editedPhotos(){return $this->hasMany(EditedPhoto::class,'id_request','id_request');}
    public function statusLogs(){return $this->hasMany(EditStatusLog::class,'id_request','id_request');}
    public function deliveries(){return $this->hasMany(PhotoDelivery::class,'id_request','id_request');}
    public function payments(){return $this->hasMany(Payment::class,'id_request','id_request');}
}