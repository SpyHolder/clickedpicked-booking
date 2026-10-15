<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Enums\RoomStatus;
use Illuminate\Validation\Rules\Enum;
class RoomRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    public function rules():array
    {
        $rules=[
            'nama_ruangan'=>'required|string|max:100',
            'deskripsi'=>'nullable|string',
            'kapasitas'=>'nullable|integer|min:1',
            'lokasi'=>'nullable|string|max:200',
        ];
        if($this->isMethod('put')||$this->isMethod('patch')){
            $rules['status_ruangan']=['nullable',new Enum(RoomStatus::class)];
        }
        return $rules;
    }
    public function messages():array
    {
        return[
            'nama_ruangan.required'=>'Nama ruangan wajib diisi',
            'nama_ruangan.max'=>'Nama ruangan maksimal 100 karakter',
            'kapasitas.integer'=>'Kapasitas harus berupa angka',
            'kapasitas.min'=>'Kapasitas minimal 1 orang',
        ];
    }
}