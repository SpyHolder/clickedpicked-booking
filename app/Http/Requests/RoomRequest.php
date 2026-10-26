<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi studio (admin create/update). Kolom sesuai ERD: nama_room, jenis_room, deskripsi, kapasitas.
 */
class RoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_room' => 'required|string|max:100',
            'jenis_room' => 'required|string|max:50',
            'deskripsi' => 'nullable|string',
            'kapasitas' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'nama_room.required' => 'Nama studio wajib diisi',
            'nama_room.max' => 'Nama studio maksimal 100 karakter',
            'jenis_room.required' => 'Jenis studio wajib diisi',
            'kapasitas.required' => 'Kapasitas wajib diisi',
            'kapasitas.integer' => 'Kapasitas harus berupa angka',
            'kapasitas.min' => 'Kapasitas minimal 1 orang',
        ];
    }
}