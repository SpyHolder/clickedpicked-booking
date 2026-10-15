<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class PaymentRequest extends FormRequest
{
    public function authorize():bool
    {
        return true;
    }
    public function rules():array
    {
        return[
            'metode_pembayaran'=>'required|string|in:qris,transfer,kartu_kredit',
            'jumlah_bayar'=>'required|numeric|min:0',
            'bukti_pembayaran'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ];
    }
    public function messages():array
    {
        return[
            'metode_pembayaran.required'=>'Metode pembayaran wajib dipilih',
            'metode_pembayaran.in'=>'Metode pembayaran tidak valid',
            'jumlah_bayar.required'=>'Jumlah pembayaran wajib diisi',
            'jumlah_bayar.numeric'=>'Jumlah harus berupa angka',
            'jumlah_bayar.min'=>'Jumlah pembayaran tidak boleh negatif',
            'bukti_pembayaran.mimes'=>'Bukti pembayaran harus berformat jpg, jpeg, png, atau pdf',
            'bukti_pembayaran.max'=>'Ukuran file maksimal 5MB',
        ];
    }
}