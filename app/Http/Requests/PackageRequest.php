<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi paket studio (admin). Kolom sesuai ERD: harga, tipe_harga, durasi_menit, deskripsi_benefit.
 */
class PackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'harga' => 'required|numeric|min:0',
            'tipe_harga' => 'required|string|max:50',
            'durasi_menit' => 'required|integer|min:1',
            'deskripsi_benefit' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'harga.required' => 'Harga wajib diisi',
            'harga.numeric' => 'Harga harus berupa angka',
            'harga.min' => 'Harga tidak boleh negatif',
            'tipe_harga.required' => 'Tipe harga wajib diisi',
            'durasi_menit.required' => 'Durasi wajib diisi',
            'durasi_menit.integer' => 'Durasi harus berupa angka',
            'durasi_menit.min' => 'Durasi minimal 1 menit',
        ];
    }
}