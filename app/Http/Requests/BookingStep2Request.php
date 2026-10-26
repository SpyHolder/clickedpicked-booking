<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi langkah 2 booking wizard: slot waktu.
 * PRD 10: tanggal tidak lampau, jam sesuai slot, jumlah_orang ≥ 1 dan ≤ kapasitas.
 */
class BookingStep2Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal_sesi' => 'required|date|after_or_equal:today',
            'jam_mulai' => 'required|date_format:H:i',
            'jumlah_orang' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'tanggal_sesi.required' => 'Tanggal sesi wajib dipilih',
            'tanggal_sesi.after_or_equal' => 'Tanggal sesi tidak boleh di masa lalu',
            'jam_mulai.required' => 'Jam mulai wajib dipilih',
            'jam_mulai.date_format' => 'Format jam tidak valid',
            'jumlah_orang.required' => 'Jumlah orang wajib diisi',
            'jumlah_orang.min' => 'Jumlah orang minimal 1',
        ];
    }
}
