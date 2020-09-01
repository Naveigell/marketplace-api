<?php

namespace App\Http\Requests\Buyer\Payment;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRefundRequestUpdate extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
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
            'order_id.required'         => 'Order id harus diisi',
            'order_id.min'              => 'Order id minimal berjumlah 20 karakter'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules() {
        return [
            'order_id'      => 'required|min:20|string'
        ];
    }
}
