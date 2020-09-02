<?php

namespace App\Http\Controllers\Api\Buyer\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Buyer\Payment\PaymentModel;
use App\Models\Buyer\Order\OrderModel;

use App\Http\Requests\Buyer\Order\ReceiveOrderRequestUpdate;

class OrderController extends Controller {

    /**
     * Variable untuk menyimpan auth user
     * @var App\Auth\Guard\TokenGuard
     */
    private $user;

    /**
     * Variable untuk menyimpan Model
     * @var Model
     */
    private $paymentModel, $orderModel;

    public function __construct(){
        $this->orderModel = new OrderModel;
        $this->paymentModel = new PaymentModel;
        $this->user = auth("user")->decodeToken("name");
    }

    /**
     * Menerima orderan
     * @param  ReceiveOrderRequestUpdate $request
     * @return json
     */
    public function receiveOrder(ReceiveOrderRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            $row = $this->orderModel->receiveOrder($user->id(), $request->input('id_order'), $request->input('order_id'));

            if ($row > 0) {
                return json([
                    "message"   => "Barang berhasil diterima",
                    "date"      => date("d-m-Y H:i")
                ]);
            }

            return error500(null, 'order', 'Barang gagal diterima');
        }

        return error401();
    }

}
