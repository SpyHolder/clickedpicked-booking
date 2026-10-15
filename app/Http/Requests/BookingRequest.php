<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use App\Utils\PhoneNormalizer;
class BookingRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    protected function prepareForValidation():void
    {
        if($this->has('nomor_wa')){
            $this->merge([
                'nomor_wa'=>PhoneNormalizer::normalize($this->nomor_wa)
            ]);
        }
    }
    public function rules():array
    {
        return[
            'id_room'=>'required|exists:rooms,id_room',
            'nama_pelanggan'=>'required|string|max:100',
            'nomor_wa'=>['required','string',function($attribute,$value,$fail){
                if(!PhoneNormalizer::isValid($value)){
                    $fail('Format nomor WhatsApp tidak valid');
                }
            }],
            'email'=>'nullable|email|max:100',
            'tanggal_mulai'=>'required|date|after_or_equal:today',
            'tanggal_selesai'=>'required|date|after_or_equal:tanggal_mulai',
            'total_harga'=>'required|numeric|min:0',
            'details'=>'required|array|min:1',
            'details.*.id_package'=>'required|exists:room_packages,id_package',
            'details.*.tanggal_sesi'=>'required|date',
            'details.*.jam_mulai'=>'required|date_format:H:i',
            'details.*.jam_selesai'=>'required|date_format:H:i|after:details.*.jam_mulai',
            'details.*.harga'=>'required|numeric|min:0',
        ];
    }
    public function messages():array
    {
        return[
            'id_room.required'=>'Ruangan wajib dipilih',
            'id_room.exists'=>'Ruangan tidak ditemukan',
            'nama_pelanggan.required'=>'Nama pelanggan wajib diisi',
            'nomor_wa.required'=>'Nomor WhatsApp wajib diisi',
            'tanggal_mulai.required'=>'Tanggal mulai wajib diisi',
            'tanggal_mulai.after_or_equal'=>'Tanggal mulai tidak boleh di masa lalu',
            'tanggal_selesai.after_or_equal'=>'Tanggal selesai tidak boleh sebelum tanggal mulai',
            'details.required'=>'Detail sesi wajib diisi',
            'details.min'=>'Minimal harus ada 1 sesi booking',
        ];
    }
}