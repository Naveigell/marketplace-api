<?php

namespace App\Http\Requests\Buyer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartRequestUpdate extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    public function messages(){
        return [
            "id_barang.required"    => "Barang harus dipilih",
            "id_barang.numeric"     => "Id barang harus berupa angka",

            "quantity.required"     => "Quantity harus diisi",
            "quantity.numeric"      => "Quantity harus berupa angka"
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            "id_barang"   => "required|numeric",
            "quantity"    => "required|numeric"
        ];
    }
}
