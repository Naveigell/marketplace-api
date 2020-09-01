<?php

namespace App\Models\Seller\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Seller\Payment\PaymentModel;

class PaymentModel extends Model {

    protected $table = 'payment';

    /**
     * Refund transaksi, hanya bisa dilakukan jika transaction status settlement
     * @param  int $id_user
     * @param  string $order_id
     * @param  string $transaction_status
     * @return int
     */
    public function cancelAndRefundBuyerOrder($id_order, $id_toko, $order_id, $transaction_status) {

        $row  = DB::update(DB::raw(
            "UPDATE payment
             INNER JOIN order_ ON order_.order_id_order = payment.order_id
             INNER JOIN epay ON epay.epay_id_akun = payment.payment_id_akun,

             --
             -- AMBIL TOTAL COUNT DAN SUM DENGAN CASE JIKA ID ORDER SAMA DENGAN PARAMETER
             -- JIKA SAMA, TAMBAHKAN SESUAI HARGA BARANG DIKALIKAN QUANTITY 
             --

             ((SELECT
               COUNT(id_order) AS total_count,
               SUM(CASE WHEN id_order = '$id_order'
                        THEN harga_barang * quantity
                        ELSE 0 END) AS total_price_back FROM order_
                                                        WHERE order_id_order = '$order_id' AND
                                                              transaction_status = 'settlement') AS orders)

             SET
             payment.transaction_status =  IF(orders.total_count = 1, 'refund', payment.transaction_status),
             order_.transaction_status = 'refund',
             order_.cancel_by = '$id_toko',
             epay.saldo = epay.saldo + orders.total_price_back

             WHERE order_.id_order = '$id_order'
             AND order_.order_id_order = '$order_id'
             AND order_.order_id_toko = '$id_toko'
             AND order_.transaction_status = 'settlement'"));

        return $row;
    }
}
