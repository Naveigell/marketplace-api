<?php

namespace App\Http\Requests\Buyer\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentCancelRequestUpdate extends FormRequest
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
     * Message jika rules tidak terpenuhi
     * @return array
     */
    public function messages() {
        return [

            'id_order.required'         => 'Id order tidak boleh kosong',
            'id_order.min'              => 'Id order minimal bernilai 1',
            'id_order.integer'          => 'Id order harus berupa angka',

            'order_id.required'         => 'Order id harus diisi',
            'order_id.min'              => 'Order id minimal berjumlah 20 karakter'
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
            // 'id_order'      => 'required|min:1|integer',
            'order_id'      => 'required|min:20|string'
        ];
    }
}
