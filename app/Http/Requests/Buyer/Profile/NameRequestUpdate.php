<?php

namespace App\Http\Requests\Buyer\Profile;

use Illuminate\Foundation\Http\FormRequest;

class NameRequestUpdate extends FormRequest
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
            'name.required'       => 'Name harus diisi',
            'name.min'            => 'Panjang name baru minimal 6',
            'name.max'            => 'Panjang name baru maksimal 30'
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
            'name'      => 'required|string|min:6|max:30'
        ];
    }
}
