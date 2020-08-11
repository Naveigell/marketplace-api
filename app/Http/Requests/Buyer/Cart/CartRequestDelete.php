<?php

namespace App\Http\Requests\Buyer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartRequestDelete extends FormRequest
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
     * Make messages for validation rule
     * @return array
     */
    public function messages(){
        return [
            "id.required"     => "Id barang tidak boleh kosong",

            "id.*.numeric"    => "Semua element array harus berupa angka",
            "id.*.min"        => "Id barang minimum 1"
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
            "id"     => "required",
            "id.*"   => "numeric|min:1"
        ];
    }
}
