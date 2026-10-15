<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'admins';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_hp',
        'is_active',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
        ];
    }

    // Relationships
    public function verifiedPayments()
    {
        return $this->hasMany(Payment::class, 'verified_by', 'id_admin');
    }

    public function createdRequests()
    {
        return $this->hasMany(PhotoEditRequest::class, 'created_by', 'id_admin');
    }

    public function verifiedRequests()
    {
        return $this->hasMany(PhotoEditRequest::class, 'verified_by', 'id_admin');
    }

    public function handledRequests()
    {
        return $this->hasMany(PhotoEditRequest::class, 'handled_by', 'id_admin');
    }

    public function statusLogs()
    {
        return $this->hasMany(EditStatusLog::class, 'changed_by', 'id_admin');
    }

    public function deliveries()
    {
        return $this->hasMany(PhotoDelivery::class, 'id_admin', 'id_admin');
    }
}