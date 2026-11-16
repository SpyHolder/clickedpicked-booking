<?php

namespace App\Http\Requests;

use App\Utils\PhoneNormalizer;
use Illuminate\Foundation\Http\FormRequest;

class PublicEditFotoRequest extends FormRequest
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

        if ($this->has('nomor_wa_confirmation')) {
            $this->merge([
                'nomor_wa_confirmation' => PhoneNormalizer::normalize((string) $this->nomor_wa_confirmation),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'nama_pelanggan' => 'required|string|max:100',
            'nomor_wa' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!PhoneNormalizer::isValid($value)) {
                    $fail('Format nomor WhatsApp tidak valid.');
                }
            }],
            'nomor_wa_confirmation' => 'required|string|same:nomor_wa',
            'jenis_layanan' => 'nullable|string|in:foto_frame,custom_frame',
            'id_frame' => 'nullable|integer|exists:frames,id_frame',
            'jumlah_slot' => 'nullable|integer|min:1|max:20',
            'items' => 'required|array|min:1|max:50',
            'items.*.nama_file' => 'required|string|max:255',
            'items.*.instruksi_edit' => 'nullable|string|max:500',
            'catatan' => 'nullable|string|max:1000',
            'persetujuan_data' => 'accepted',
            'website' => 'nullable|max:0', // Honeypot anti-spam
        ];
    }

    public function messages(): array
    {
        return [
            'nama_pelanggan.required' => 'Nama lengkap wajib diisi.',
            'nama_pelanggan.max' => 'Nama lengkap maksimal 100 karakter.',
            'nomor_wa.required' => 'Nomor WhatsApp wajib diisi.',
            'nomor_wa_confirmation.required' => 'Konfirmasi nomor WhatsApp wajib diisi.',
            'nomor_wa_confirmation.same' => 'Konfirmasi nomor WhatsApp harus sama dengan nomor WhatsApp.',
            'id_frame.exists' => 'Frame yang dipilih tidak ditemukan.',
            'items.required' => 'Minimal satu kode foto wajib diisi.',
            'items.min' => 'Minimal satu kode foto wajib diisi.',
            'items.max' => 'Maksimal 50 foto per permintaan.',
            'items.*.nama_file.required' => 'Kode atau nomor file foto dari Google Drive wajib diisi.',
            'items.*.nama_file.max' => 'Kode atau nomor file foto maksimal 255 karakter.',
            'items.*.instruksi_edit.max' => 'Instruksi edit maksimal 500 karakter per slot.',
            'catatan.max' => 'Catatan tambahan maksimal 1000 karakter.',
            'persetujuan_data.accepted' => 'Anda wajib menyetujui penggunaan nomor WhatsApp untuk pengiriman hasil edit.',
            'website.max' => 'Permintaan terdeteksi sebagai spam.',
        ];
    }
}
