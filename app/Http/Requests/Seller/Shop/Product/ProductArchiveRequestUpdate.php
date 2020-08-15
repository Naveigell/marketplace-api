<?php

namespace App\Http\Requests\Seller\Shop\Product;

use Illuminate\Foundation\Http\FormRequest;

class ProductArchiveRequestUpdate extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    public function messages() {
        return [
            'id_barang.required'      => 'Id barang harus diisi',
            'id_barang.integer'       => 'Id barang harus berupa angka',
            'id_barang.min'           => 'Id barang minimal 1'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'id_barang'       => 'required|integer|min:1'
        ];
    }
}
