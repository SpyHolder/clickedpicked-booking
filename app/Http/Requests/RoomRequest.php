<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi studio (admin create/update).
 */
class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (!$this->has('cabang') || empty($this->cabang)) {
            $this->merge(['cabang' => 'batu_lima']);
        }
    }

    public function rules(): array
    {
        return [
            'nama_room' => 'required|string|max:100',
            'cabang' => 'required|string|in:batu_lima,jalan_merdeka',
            'jenis_room' => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
            'kapasitas' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_room.required' => 'Nama studio wajib diisi (contoh: Room 1)',
            'nama_room.max' => 'Nama studio maksimal 100 karakter',
            'cabang.required' => 'Pilih cabang studio',
            'cabang.in' => 'Cabang yang dipilih tidak valid (pilih Batu 5 atau Jalan Merdeka)',
            'jenis_room.required' => 'Pilih jenis studio',
            'kapasitas.required' => 'Kapasitas maksimal wajib diisi',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 1 orang',
        ];
    }
}
