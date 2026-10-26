<?php

namespace App\Http\Requests;

use App\Utils\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi langkah 1 booking wizard: data diri, studio, paket.
 * PRD 10: nama wajib max 100, no HP valid & dinormalisasi, email opsional, studio aktif, paket milik studio.
 */
class BookingStep1Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('no_hp_pelanggan')) {
            $this->merge([
                'no_hp_pelanggan' => PhoneNormalizer::normalize($this->no_hp_pelanggan),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => 'required|string|max:100',
            'no_hp_pelanggan' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!PhoneNormalizer::isValid($value)) {
                    $fail('Format nomor HP tidak valid');
                }
            }],
            'email_pelanggan' => 'nullable|email|max:100',
            'id_room' => 'required|exists:rooms,id_room',
            'id_package' => 'required|exists:room_packages,id_package',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 100 karakter',
            'no_hp_pelanggan.required' => 'Nomor HP wajib diisi',
            'email_pelanggan.email' => 'Format email tidak valid',
            'id_room.required' => 'Studio wajib dipilih',
            'id_room.exists' => 'Studio tidak ditemukan',
            'id_package.required' => 'Paket wajib dipilih',
            'id_package.exists' => 'Paket tidak ditemukan',
        ];
    }
}
