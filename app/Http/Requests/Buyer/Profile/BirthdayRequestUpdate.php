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
            'day.required'          => 'Day harus diisi',
            'day.integer'           => 'Day harus berupa angka',
            'day.between'           => 'Day harus diantara 1 hingga 31',

            'month.required'        => 'Bulan harus diisi',
            'month.integer'         => 'Bulan harus berupa angka',
            'month.between'         => 'Bulan harus diantara 1 hingga 12',

            'year.required'         => 'Tahun harus diisi',
            'year.integer'          => 'Tahun harus berupa angka',
            'year.between'          => 'Tahun harus diantara 1940 hingga '.now()->year
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
