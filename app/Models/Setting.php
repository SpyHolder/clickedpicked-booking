<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';
    protected $primaryKey = 'id_setting';
    public $timestamps = false;

    protected $fillable = ['key', 'value', 'tipe', 'deskripsi', 'updated_at'];

    protected function casts(): array
    {
        return [
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Ambil nilai setting berdasarkan key.
     */
    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Simpan atau perbarui setting.
     */
    public static function setValue(string $key, string $value, string $tipe = 'text', ?string $deskripsi = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'tipe' => $tipe, 'deskripsi' => $deskripsi, 'updated_at' => now()]
        );
    }
}