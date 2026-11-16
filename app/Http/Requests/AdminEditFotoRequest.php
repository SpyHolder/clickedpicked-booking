<?php

namespace App\Http\Requests;

use App\Utils\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class AdminEditFotoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('nomor_wa')) {
            $this->merge([
                'nomor_wa' => PhoneNormalizer::normalize((string) $this->nomor_wa),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'id_room' => 'required|exists:rooms,id_room',
            'kode_booking' => 'nullable|string|max:50|exists:bookings,kode_booking',
            'nama_pelanggan' => 'required|string|max:100',
            'nomor_wa' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!PhoneNormalizer::isValid($value)) {
                    $fail('Format nomor WhatsApp tidak valid.');
                }
            }],
            'jenis_layanan' => 'nullable|string|in:foto_frame,custom_frame',
            'id_frame' => 'nullable|integer|exists:frames,id_frame',
            'jumlah_slot' => 'nullable|integer|min:1|max:20',
            'items' => 'required|array|min:1|max:50',
            'items.*.nama_file' => 'required|string|max:255',
            'items.*.instruksi_edit' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_room.required' => 'Studio wajib dipilih.',
            'id_room.exists' => 'Studio tidak ditemukan.',
            'kode_booking.exists' => 'Kode booking tidak ditemukan.',
            'nama_pelanggan.required' => 'Nama pelanggan wajib diisi.',
            'nama_pelanggan.max' => 'Nama pelanggan maksimal 100 karakter.',
            'nomor_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'id_frame.exists' => 'Frame yang dipilih tidak ditemukan.',
            'items.required' => 'Minimal satu baris kode foto wajib diisi.',
            'items.min' => 'Minimal satu baris kode foto wajib diisi.',
            'items.max' => 'Maksimal 50 foto per permintaan.',
            'items.*.nama_file.required' => 'Kode atau nomor file foto wajib diisi.',
            'items.*.nama_file.max' => 'Kode atau nomor file foto maksimal 255 karakter.',
            'catatan.max' => 'Catatan maksimal 1000 karakter.',
        ];
    }
}
