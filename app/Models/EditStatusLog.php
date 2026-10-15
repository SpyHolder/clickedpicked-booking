<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class EditStatusLog extends Model
{
    protected $table = 'edit_status_logs';
    protected $primaryKey = 'id_log';
    public $timestamps = false;
    protected $fillable = ['id_request', 'changed_by', 'status_lama', 'status_baru', 'changed_at'];
    protected function casts()
    {
        return ['changed_at' => 'datetime'];
    }
    public function request()
    {
        return $this->belongsTo(PhotoEditRequest::class, 'id_request', 'id_request');
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'changed_by', 'id_admin');
    }
}
