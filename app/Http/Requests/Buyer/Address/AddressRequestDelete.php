<?php

namespace App\Http\Requests\Buyer\Address;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequestDelete extends FormRequest
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

    public function messages() {
        return [
            'id_alamat.required'      => 'Id alamat harus diisi',
            'id_alamat.integer'       => 'Id alamat harus berupa angka',
            'id_alamat.min'           => 'Id alamat minimum 1'
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
            'id_alamat'   => 'required|integer|min:1'
        ];
    }
}
