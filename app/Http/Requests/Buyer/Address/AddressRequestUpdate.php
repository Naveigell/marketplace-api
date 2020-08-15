<?php

namespace App\Http\Requests\Buyer\Address;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequestUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Message untuk validasi rules
     * @return array
     */
    public function messages() {
        return [
            'id_alamat.required'        => 'Id alamat tidak boleh kosong',
            'id_alamat.integer'         => 'Id alamat harus berupa angka',
            'id_alamat.min'             => 'Id alamat minimal 1',

            'id_kota.required'          => 'Id kota tidak boleh kosong',
            'id_kota.integer'           => 'Id kota harus berupa angka',
            'id_kota.between'           => 'Id kota harus antara 1 dan 501',

            'nama_alamat.required'      => 'Nama alamat tidak boleh kosong',
            'nama_alamat.min'           => 'Panjang nama alamat minimal 6',
            'nama_alamat.max'           => 'Panjang nama alamat maksimal 100',

            'nama_penerima.required'    => 'Nama penerima tidak boleh kosong',
            'nama_penerima.min'         => 'Panjang nama penerima minimal 6',
            'nama_penerima.max'         => 'Panjang nama penerima maksimal 100',

            'no_hp_penerima.required'   => 'No hp penerima tidak boleh kosong',
            'no_hp_penerima.min'        => 'Panjang no hp penerima minimal 8',
            'no_hp_penerima.max'        => 'Panjang no hp penerima maksimal 20',

            'alamat_lengkap.required'   => 'Alamat lengkap tidak boleh kosong',
            'alamat_lengkap.min'        => 'Panjang alamat lengkap minimal 6',
            'alamat_lengkap.max'        => 'Panjang alamat lengkap maksimal 100',
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'id_alamat'       => 'required|integer|min:1',
            'id_kota'         => 'required|integer|between:1,501',
            'nama_alamat'     => 'required|string|min:6|max:100',
            'nama_penerima'   => 'required|string|min:6|max:100',
            'no_hp_penerima'  => 'required|string|min:8|max:20',
            'alamat_lengkap'  => 'required|string|min:6|max:255'
        ];
    }
}
