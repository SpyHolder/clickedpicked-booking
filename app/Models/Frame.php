<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Frame extends Model
{
    protected $table = 'frames';
    protected $primaryKey = 'id_frame';

    protected $fillable = [
        'kode_frame',
        'nama_frame',
        'jumlah_slot',
        'warna_aksen',
        'preview_path',
        'deskripsi',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'jumlah_slot' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function photoEditRequests()
    {
        return $this->hasMany(PhotoEditRequest::class, 'id_frame', 'id_frame');
    }
}
