<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi upload bukti bayar (PRD 10 & Keputusan #4).
 * Pelanggan hanya mengunggah file, tanpa isi nominal/metode.
 */
class PaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'bukti_pembayaran' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'bukti_pembayaran.required' => 'Bukti pembayaran wajib diupload',
            'bukti_pembayaran.file' => 'Bukti pembayaran harus berupa file',
            'bukti_pembayaran.mimes' => 'Bukti pembayaran harus berformat JPG, JPEG, PNG, atau PDF',
            'bukti_pembayaran.max' => 'Ukuran file maksimal 5MB',
        ];
    }
}
