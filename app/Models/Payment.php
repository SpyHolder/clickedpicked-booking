<?php
namespace App\Models;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Model;
class Payment extends Model
{
    protected $table = 'payments';
    protected $primaryKey = 'id_payment';
    protected $fillable = ['id_booking', 'id_request', 'verified_by', 'jenis_pembayaran', 'metode_pembayaran', 'jumlah_bayar', 'tanggal_bayar', 'status_pembayaran', 'bukti_pembayaran'];
    protected function casts()
    {
        return ['status_pembayaran' => PaymentStatus::class, 'tanggal_bayar' => 'date', 'jumlah_bayar' => 'decimal:2'];
    }
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'id_booking', 'id_booking');
    }
    public function request()
    {
        return $this->belongsTo(PhotoEditRequest::class, 'id_request', 'id_request');
    }
    public function verifier()
    {
        return $this->belongsTo(Admin::class, 'verified_by', 'id_admin');
    }
}
