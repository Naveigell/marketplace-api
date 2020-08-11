<?php

namespace App\Http\Requests\Buyer\Cart;

use Illuminate\Foundation\Http\FormRequest;

class CartRequestToggle extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */

    public function authorize() {
        return true;
    }

    /**
     * Make messages for validation rule
     *
     * @return array
     */
    public function messages() {
        return [
            "id.required"       => "Id barang tidak boleh kosong",

            "id.*.numeric"      => "Semua element array harus berupa angka",
            "id.*.min"          => "Id barang minimum 1",

            "toggle.required"   => "Nilai toggle tidak boleh kosong",
            "toggle.integer"    => "Toggle harus berupa angka",
            "toggle.between"    => "Nilai toggle harus diantara 0 atau 1"
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
            "id"      => "required",
            "id.*"    => "numeric|min:1",
            "toggle"  => "required|integer|between:0,1"
        ];
    }
}
