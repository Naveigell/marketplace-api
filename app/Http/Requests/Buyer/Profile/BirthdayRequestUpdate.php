<?php

namespace App\Http\Requests\Buyer\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

class BirthdayRequestUpdate extends FormRequest {
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

        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'day'     => 'required|integer|between:1,31',
            'month'   => 'required|integer|between:1,12',
            'year'    => 'required|integer|between:1940,'.now()->year
        ];
    }
}
