<?php

namespace App\Http\Requests\Seller\Shop\Order;

use Illuminate\Foundation\Http\FormRequest;

class CancelOrderRequestUpdate extends FormRequest {
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
            'id_order.required'       => 'Id order tidak boleh kosong',
            'id_order.integer'        => 'Id order harus berupa angka',
            'id_order.min'            => 'Nilai id order minimal 1',

            'order_id.required'       => 'Order id tidak boleh kosong',
            'order_id.min'            => 'Panjang order id minimal 6'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'id_order'      => 'required|integer|min:1',
            'order_id'      => 'required|min:6'
        ];
    }
}
