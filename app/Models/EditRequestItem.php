<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EditRequestItem extends Model
{
    protected $table='edit_request_items';
    protected $primaryKey='id_item';
    protected $fillable=['id_request','nama_file','instruksi_edit'];
    public function request(){return $this->belongsTo(PhotoEditRequest::class,'id_request','id_request');}
    public function editedPhotos(){return $this->hasMany(EditedPhoto::class,'id_item','id_item');}
}