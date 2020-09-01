<?php

namespace App\Http\Controllers\Api\Buyer\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Veritrans_Config;
use Veritrans_Snap;
use Veritrans_Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

use App\Models\Buyer\Payment\PaymentModel;
use App\Models\Buyer\Order\OrderModel;
use App\Models\Buyer\Cart\CartModel;
use App\Models\Users\UserModel;

use App\Http\Requests\Buyer\Payment\PaymentCancelRequestUpdate;
use App\Http\Requests\Buyer\Payment\PaymentRefundRequestUpdate;

class PaymentController extends Controller {

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
     * Edit nomor ponsel untuk parsing ke midtrans
     * @param  string $number
     * @return string
     */
    public function phoneNumber($number) {
        if (is_string($number)) {
            if ($number[0] == "0") {
                return "+62".substr($number, 1, strlen($number));
            }
        }
        return $number;
    }

    /**
     * Generate random id untuk order id, dimulai dari
     * random string sebanyak 20 karakter diikuti dengan id user
     * kemudian dibatasi titik lalu dilanjut dengan waktu server saat ini
     * @param  int $id
     * @return string
     */
    public function orderId($id) {
        return Str::random(20).date("$id.dmYHis");
    }

    /**
     * Ambil payment berdasarkan id user saat ini
     * @return json
     */
    public function getPayment() {
        if ($this->user->exists()) {
            $user = $this->user;
            $payments = $this->paymentModel->getPayment($user->id());

            // jadikan array, bukan object
            $collections = collect($payments)->map(function($item) {
                return $item->order_id;
            });

            return json([
                "payments"  => $collections,
                "total"     => count($collections)
            ]);
        }
        return error401();
    }

    /**
     * Melakukan payment
     * @param  Request $request
     * @return redirect with snaptoken|no snaptoken
     */
    public function pay(Request $request) {

        if ($this->user->exists()) {
            Veritrans_Config::$serverKey = config('services.midtrans.serverKey');
            Veritrans_Config::$isProduction = config('services.midtrans.isProduction');
            Veritrans_Config::$isSanitized = config('services.midtrans.isSanitized');
            Veritrans_Config::$is3ds = config('services.midtrans.is3ds');

            $cartModel  = new CartModel;
            $userModel  = new UserModel;

            $cart       = $cartModel->getCartForPayment($this->user->id());
            $user       = $userModel->getCustomerDetail($this->user->id());

            // jika tidak ada keranjang
            if (count($cart) <= 0) {
                return redirect('/')->with('message', 'Tidak ada keranjang ditemukan');
            }

            // jika tidak ada user
            if (count((array) $user) <= 0) {
                return redirect('/')->with('message', 'Anda belum login');
            }

            $transaction_details = [
                'order_id'        => $this->orderId($this->user->id()),
                'gross_amount'    => count($cart)
            ];

            $customer_details   = [
                'first_name'    => $user->akun_username,
                'phone'         => $this->phoneNumber($user->nomor_hp),
                'email'         => $user->email,
            ];

            $items = array();

            // masukkan list keranjang
            foreach ($cart as $c) {
                array_push($items, [
                    'id'        => $c->id_barang,
                    'price'     => $c->harga,
                    'quantity'  => $c->quantity,
                    'name'      => $c->nama_barang
                ]);
            }

            $payload = [
                'transaction_details' => $transaction_details,
                'customer_details' => $customer_details,
                'item_details' => $items,
                'metadata'  => [
                    "id"  => $this->user->id()
                ],
                'custom_field1' => $this->user->id()
            ];

            $snapToken = Veritrans_Snap::getSnapToken($payload);

            return redirect('/')->with('token', $snapToken);
        }

        return redirect('/')->with('message', 'Anda belum login');
    }

    /**
     * Ambil midtrans server key lalu di encode
     * @return string
     */
    private function getEncodedMidtransServerKey() {
        $serverKey  = config('services.midtrans.serverKey');
        $serverKey  = base64_encode($serverKey.":");

        return $serverKey;
    }

    /**
     * Cancel payment lalu ambil response nya
     * @param  int $order_id  [description]
     * @param  string $serverKey
     * @return object
     */
    private function cancelPaymentResponse($order_id, $serverKey) {
        $response = Http::withHeaders([
            'Authorization' => "Basic $serverKey",
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ])->post("https://api.sandbox.midtrans.com/v2/$order_id/cancel");

        $response = json_decode($response->body());

        return $response;
    }

    /**
     * Refund payment lalu ambil response nya
     * @param  int $order_id
     * @param  string $serverKey
     * @return object
     */
    public function refundPaymentResponse($order_id, $serverKey) {
        $response = Http::withHeaders([
            'Authorization' => "Basic $serverKey",
            "Accept" => "application/json",
            "Content-Type" => "application/json",
        ])->post("https://api.sandbox.midtrans.com/v2/$order_id/refund");

        $response = json_decode($response->body());

        return $response;
    }

    /**
     * Refund payment, hanya bisa dilakukan jika status transaksinya settlement
     * @param  PaymentRefundRequestUpdate $request
     * @return json
     */
    public function refundPayment(PaymentRefundRequestUpdate $request) {
        if ($this->user->exists()) {
            $user             = $this->user;
            $order_id         = $request->input('order_id');

            $serverKey  = $this->getEncodedMidtransServerKey();

            // cek jika payment memang valid
            $exists = $this->paymentModel->isPaymentExistsWithStatus($user->id(), $order_id, 'settlement');
            if ($exists) {

                // REFUND LANGSUNG KE MIDTRANS BELUM TERSEDIA
                // SAAT INI HANYA MENGECEK STATUS SETTLEMENT SAJA UNTUK
                // PENGEMBALIAN DANA
                //
                //
                //
                //
                //
                //
                //
                //
                // END OF FEATURE MESSAGE

                // Lakukan refund TANPA melalui pihak ketiga
                $row = $this->paymentModel->refundPayment($user->id(), $order_id, 'refund');

                if ($row > 0) {
                    return json([
                        "message"     => "Pengembalian dana berhasil dilakukan dan dana dikembalikan ke saldo {App}",
                        "date"        => date("d-m-Y H:i")
                    ]);
                }
                return error500(null, "transaksi", "Terjadi masalah saat melakukan pengembalian dana");
            }
            return error404(null, "transaksi", "Tidak ada transaksi yang ditemukan");
        }
        return error401();
    }

    /**
     * Cancel pembayaran, hanya bisa dilakukan buyer
     * @param  PaymentCancelRequestUpdate $request
     * @return json
     */
    public function cancelPayment(PaymentCancelRequestUpdate $request) {
        if ($this->user->exists()) {
            $user             = $this->user;
            $order_id         = $request->input('order_id');

            $serverKey  = $this->getEncodedMidtransServerKey();

            // cek jika order memang valid
            $exists = $this->paymentModel->isPaymentExistsWithStatus($user->id(), $order_id, 'pending');
            if ($exists) {

                // lalu lakukan cancel ke midtrans
                $response = $this->cancelPaymentResponse($order_id, $serverKey);
                if ($response->status_code == 200) {

                    // lalu lakukan update di database merchant
                    $row = $this->paymentModel->cancelPayment($user->id(), $order_id, $response->transaction_status);
                    if ($row > 0) {
                        return json([
                            "message"     => "Transaksi berhasil dibatalkan",
                            "date"        => now()
                        ]);
                    }
                }
                return error500(null, "transaksi", "Terjadi masalah saat membatalkan transaksi");
            }
            return error404(null, "transaksi", "Tidak ada transaksi yang ditemukan");
        }
        return error401();
    }

    /**
     * Handle notifikasi yang didapatkan dari midtrans
     * @param  Request $request
     * @return void
     */
    public function notificationHandling(Request $request) {

        // encrypt key
        $serverKey = config('services.midtrans.serverKey');
        $serverSignatureKey = hash('sha512', ($request->order_id.$request->status_code.$request->gross_amount.$serverKey));

        if ($request->signature_key == $serverSignatureKey) {

            $userID               = $request->custom_field1;
            $orderID              = $request->order_id;
            $transactionStatus    = $request->transaction_status;

            // jika status cancel, maka return saja, karena untuk status cancel akan
            // di handle oleh fungsi lain
            if ($transactionStatus == 'cancel') {
                return;
            }

            $row = $this->paymentModel->updatePayment(
                 $orderID,
                 $userID,
                 $request->gross_amount,
                 $request->payment_type,
                 $transactionStatus
            );

            // has updated
            if (!$row->wasRecentlyCreated && $row->wasChanged()) {
                error_log("Update $orderID dengan status $transactionStatus berhasil");

                $row = $this->orderModel->updateOrderTransactionStatus($userID, $orderID, $transactionStatus);
                if ($row > 0) {
                    error_log("Order table with id $orderID and transaction $transactionStatus has been updated");
                }
            }
            // has created
            else if($row->wasRecentlyCreated) {
                error_log("Created $orderID dengan status $transactionStatus berhasil");

                $inserted = $this->orderModel->insertOrder($request->custom_field1, $orderID, $transactionStatus);
                if ($inserted) {
                    error_log("Order table with id $orderID and transaction $transactionStatus has been created");
                }
            }
        }
    }

    public function clientPending() {
        $order_id   = request('order_id');
        if (is_null($order_id)) {
            return error422(null, 'order_id', 'Order id tidak boleh kosong');
        }

        $response = Http::withBasicAuth(config('services.midtrans.serverKey'), '')->get("https://api.sandbox.midtrans.com/v2/$order_id/status");
        return $response->json();
    }

    public function a(PaymentCancelRequestUpdate $request) {
        if ($this->user->exists()) {
            $user       = $this->user;
            $order_id   = $request->order_id;
            $id_order   = $request->id_order;

            $serverKey  = config('services.midtrans.serverKey');
            $serverKey  = base64_encode($serverKey.":");

            $order = $this->paymentModel->getPaymentUserAndSellerUser($id_order, $order_id);

            if (empty($order)) {
                return error404(null, "order", "Tidak ada order yang ditemukan");
            }

            // jika yang mengcancel adalah seller
            if ($order->id_seller == $user->id()) {
                $response = $this->cancelPaymentResponse($order_id, $serverKey);

                if ($response->status_code == 200) {

                    // update transasction status dan update cancel by
                    $row = $this->orderModel->update_Transaction_Status_And_Cancel_By_Seller_Shop($id_order, $order_id, $order->id_toko);
                    if ($row > 0) {
                        return json([
                            'message'     => 'Transaksi berhasil di batalkan',
                            'date'        => now()
                        ]);
                    }
                }
            }
            // jika yang mengcancel adalah buyer
            else if ($order->id_buyer == $user->id()) {
                $response = $this->cancelPaymentResponse($order_id, $serverKey);

                if ($response->status_code == 200) {
                    $row = $this->paymentModel->cancelPaymentAndOrder($id_order, $order_id, $user->id());
                }
            }

            return ($order);

            // $response = Http::withHeaders([
            //     'Authorization' => "Basic $serverKey",
            //     "Accept" => "application/json",
            //     "Content-Type" => "application/json",
            // ])->post("https://api.sandbox.midtrans.com/v2/$order_id/cancel");

            // $response = json_decode($response->body());

            // if ($response->status_code == 200) {
            //
            // }

            // return $response->json();
        }
        return error401();
    }

}
