<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PackageRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    public function rules():array
    {
        return[
            'nama_paket'=>'required|string|max:100',
            'durasi_jam'=>'required|integer|min:1',
            'harga'=>'required|numeric|min:0',
            'inklusi'=>'nullable|string',
        ];
    }
    public function messages():array
    {
        return[
            'nama_paket.required'=>'Nama paket wajib diisi',
            'durasi_jam.required'=>'Durasi jam wajib diisi',
            'durasi_jam.integer'=>'Durasi harus berupa angka',
            'durasi_jam.min'=>'Durasi minimal 1 jam',
            'harga.required'=>'Harga wajib diisi',
            'harga.numeric'=>'Harga harus berupa angka',
            'harga.min'=>'Harga tidak boleh negatif',
        ];
    }
}