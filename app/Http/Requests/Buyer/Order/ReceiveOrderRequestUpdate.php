<?php

namespace App\Http\Requests\Buyer\Order;

use Illuminate\Foundation\Http\FormRequest;

class ReceiveOrderRequestUpdate extends FormRequest
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
            'id_order.required'       => 'id order tidak boleh kosong',
            'id_order.min'            => 'id order minimal 1',
            'id_order.integer'        => 'id order harus berupa angka',

            'order_id.required'       => 'order id tidak boleh kosong',
            'order_id.min'            => 'panjang order id minimal 6',
            'order_id.string'         => 'order id harus berupa string'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'id_order'      => 'required|min:1|integer',
            'order_id'      => 'required|min:6|string'
        ];
    }
}
