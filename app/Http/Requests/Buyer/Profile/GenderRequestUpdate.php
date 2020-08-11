<?php

namespace App\Http\Requests\Buyer\Profile;

use Illuminate\Foundation\Http\FormRequest;

class GenderRequestUpdate extends FormRequest
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
            'gender.required'     => 'Gender harus diisi',
            'gender.integer'      => 'Gender harus berupa angka',
            'gender.between'      => 'Gender harus antara 1 dan 2'
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
            'gender'      => 'required|integer|between:1,2'
        ];
    }
}
