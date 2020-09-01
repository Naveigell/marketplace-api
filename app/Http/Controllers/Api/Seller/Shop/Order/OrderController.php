<?php

namespace App\Http\Controllers\Api\Seller\Shop\Order;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Seller\Shop\Order\CancelOrderRequestUpdate;

use App\Models\Seller\Payment\PaymentController;

use App\Models\Seller\Payment\PaymentModel;
use App\Models\Seller\Order\OrderModel;

class OrderController extends Controller {
    /**
     * Variable untuk menyimpan auth user
     * @var App\Auth\Guard\TokenGuard
     */
    private $user;

    /**
     * Variable menyimpan model eloquent
     * @var App\Models\Seller\Order\OrderModel
     */
    private $orderModel;

    /**
     * Variable menyimpan eloquent PaymentModel
     * @var App\Models\Seller\Payment\PaymentModel
     */
    private $paymentModel;

    public function __construct() {
        $this->user = auth("user")->decodeToken("name");
        $this->orderModel = new OrderModel;
        $this->paymentModel = new PaymentModel;
    }

    /**
     * Ambil semua order yang dimiliki oleh toko
     * @param  Request $request
     * @return json
     */
    public function getAllOrders() {
        if ($this->user->exists()) {
            $user = $this->user;
            $toko = $user->toko();

            $orders = $this->orderModel->getOrderByShopId($toko->id());
            $data   = [];

            foreach ($orders as $order) {
                $barang   = new \stdClass;
                $pesanan  = new \stdClass;
                $alamat   = new \stdClass;

                $barang->nama_barang        = $order->nama_barang;
                $barang->slug               = $order->slug;
                $barang->harga              = $order->harga;
                $barang->diskon             = $order->diskon;
                $barang->stok               = $order->stok_barang;
                $barang->berat              = $order->berat_barang;

                $pesanan->id_pesanan        = $order->order_id_order;
                $pesanan->jumlah            = $order->quantity;
                $pesanan->message           = $order->message;
                $pesanan->status_transaksi  = $order->transaction_status;

                $alamat->provinsi           = $order->nama_provinsi;
                $alamat->tipe               = $order->tipe;
                $alamat->nama_kota          = $order->nama_kota;
                $alamat->postal_code        = $order->postal_code;
                $alamat->nama_alamat        = $order->nama_alamat;
                $alamat->nama_penerima      = $order->nama_penerima;
                $alamat->no_hp_penerima     = $order->no_hp_penerima;
                $alamat->alamat_lengkap     = $order->alamat_lengkap;

                array_push($data, [
                    "barang"    => $barang,
                    "pesanan"   => $pesanan,
                    "alamat"    => $alamat
                ]);
            }

            return json([
                "orders"    => $data,
                "total"     => count($data),
                "date"      => date("d-m-Y H:i")
            ]);
        }
        return error401();
    }

    /**
     * Cancel order yang diterima dari buyer
     * @param  App\Http\Requests\Seller\Shop\Order\CancelOrderRequestUpdate $request
     * @return json
     */
    public function cancelOrder(CancelOrderRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;
            $toko = $user->toko();

            // FIXME: MENGUBAH 1 ORDER TETAPI EPAY BERTAMBAH SEJUMLAH GROSS AMOUNT
            $row = $this->paymentModel->cancelAndRefundBuyerOrder($request->id_order, $toko->id(), $request->order_id, 'refund');

            return json($row);
        }
        return error401();
    }
}
