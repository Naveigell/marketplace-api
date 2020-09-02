<?php

namespace App\Models\Buyer\Payment;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Buyer\Payment\PaymentModel;

class PaymentModel extends Model {
    protected $table = 'payment';
    protected $fillable = ['order_id', 'gross_amount', 'payment_id_akun', 'payment_type', 'transaction_status'];
    protected $primaryKey = 'id_payment';

    /**
     * Update payment melalui notification yang dikirim oleh midtrans
     * @param  int $order_id
     * @param  int $id_user
     * @param  int $gross_amount
     * @param  string $payment_type
     * @param  string $status
     * @return PaymentModel
     */
    public function updatePayment($order_id, $id_user, $gross_amount, $payment_type, $status) {
        $instance = PaymentModel::updateOrCreate(
            ["order_id" => $order_id],
            ["gross_amount" => $gross_amount, "payment_id_akun" => $id_user, "payment_type" => $payment_type, "transaction_status" => $status, "created_at" => Carbon::now(), "updated_at" => Carbon::now()]
        );
        $instance->save();

        return $instance;
    }

    public function getPaymentUserAndSellerUser($id_order, $order_id) {
        return PaymentModel::select(['akun.id_akun AS id_seller', 'order_.order_id_buyer AS id_buyer', 'toko.id_toko AS id_toko'])
                           ->join('order_', 'order_.order_id_order', '=', 'payment.order_id')
                           ->join('toko', 'toko.id_toko', '=', 'order_.order_id_toko')
                           ->join('akun', 'akun.id_akun', '=', 'toko.toko_id_akun')
                           ->where([
                              "id_order"          => $id_order,
                              "order_id_order"    => $order_id
                           ])->first();
    }

    /**
     * Cancel payment yang sesuai dengan order id
     * @param  int $id_user
     * @param  string $order_id
     * @param  string $transaction_status
     * @return int
     */
    public function cancelPayment($id_user, $order_id, $transaction_status) {
        return PaymentModel::join('order_', 'order_.order_id_order', '=', 'payment.order_id')
                           ->where([
                              'payment.order_id'            => $order_id,
                              'payment.payment_id_akun'     => $id_user,
                              'payment.transaction_status'  => 'pending'
                           ])
                           ->update([
                              'payment.transaction_status'  => $transaction_status,
                              'payment.cancel_by'           => $id_user,
                              'order_.transaction_status'   => $transaction_status,
                              'order_.cancel_by'            => $id_user
                           ]);
    }

    /**
     * Refund transaksi, hanya bisa dilakukan jika transaction status settlement
     * @param  int $id_user
     * @param  string $order_id
     * @param  string $transaction_status
     * @return int
     */
    public function refundPayment($id_user, $order_id, $transaction_status) {

        $row  = DB::update(DB::raw(
            "UPDATE payment
             INNER JOIN order_ ON order_.order_id_order = payment.order_id
             INNER JOIN epay ON epay.epay_id_akun = payment.payment_id_akun,

             --
             -- AMBIL TOTAL COUNT DAN SUM DENGAN CASE JIKA ID ORDER SAMA DENGAN PARAMETER
             -- JIKA SAMA, TAMBAHKAN SESUAI HARGA BARANG DIKALIKAN QUANTITY
             --

             ((SELECT SUM(harga_barang * quantity) AS total_price_back FROM  order_
                                                                       WHERE order_id_order = '$order_id' AND
                                                                             cancel_by = 0 AND
                                                                             has_sent = 0 AND
                                                                             has_received = 0 AND
                                                                             transaction_status = 'settlement') AS orders)

             SET
             payment.transaction_status = 'refund',
             payment.refund_by = '$id_user',
             order_.transaction_status = 'refund',
             epay.saldo = epay.saldo + orders.total_price_back

             WHERE payment.order_id = '$order_id'
             AND order_.cancel_by = 0
             AND order_.has_sent = 0
             AND order_.has_received = 0
             AND payment.payment_id_akun = '$id_user'
             AND payment.transaction_status = 'settlement'"));

        return $row;
    }

    /**
     * Cek jika payment exists untuk bisa membatalkan payment
     * @param  int  $id_user
     * @param  string  $order_id
     * @return boolean
     */
    public function isPaymentExistsWithStatus($id_user, $order_id, $transaction_status) {
        return PaymentModel::where([
            'order_id'            => $order_id,
            'payment_id_akun'     => $id_user,
            'transaction_status'  => $transaction_status
        ])->exists();
    }

    /**
     * Batalkan orderan dari user
     * @param  int $id_user
     * @param  string $order_id
     * @param  int $cancel_by
     * @return int
     */
    public function cancelOrderByBuyer($id_user, $order_id, $cancel_by) {
        return PaymentModel::where([
            'order_id'            => $order_id,
            'payment_id_akun'     => $id_user
        ])->update([
            'cancel_by'           => $cancel_by
        ]);
    }

    /**
     * Ambil semua payment yang ada berdasarkan id user
     * @param  int $id_user
     * @return array
     */
    public function getPayment($id_user) {
        return PaymentModel::select(['order_id'])->where('payment_id_akun', $id_user)->get();
    }
}
