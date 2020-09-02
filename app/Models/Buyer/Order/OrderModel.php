<?php

namespace App\Models\Buyer\Order;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Buyer\Order\OrderModel;

class OrderModel extends Model {
    protected $table = 'order_';

    /**
     * Update status transaksi
     * @param  int $id_user
     * @param  string $order_id
     * @param  string $transaction_status
     * @return int mengembalikan banyaknya row
     */
    public function updateOrderTransactionStatus($id_user, $order_id, $transaction_status) {
        return OrderModel::where([
            'order_id_order'      => $order_id,
            'order_id_buyer'      => $id_user
        ])->update([
            'transaction_status'  => $transaction_status
        ]);
    }

    public function update_Transaction_Status_And_Cancel_By_Seller_Shop($id_order, $order_id, $id_shop) {
        return OrderModel::where([
            'id_order'            => $id_order,
            'order_id_order'      => $order_id,
            'transaction_status'  => 'pending'
        ])->update([
            'transaction_status'  => 'cancel',
            'cancel_by'           => $id_shop
        ]);
    }

    /**
     * Fungsi untuk mengubah received dalam database yang artinya
     * buyer sudah menerima orderan
     * @param  int $id_user
     * @param  int $id_order
     * @param  string $order_id
     * @return int
     */
    public function receiveOrder($id_user, $id_order, $order_id) {
        return DB::table('epay')->join('akun', 'akun.id_akun', '=', 'epay.epay_id_akun')
                                ->join('toko', 'toko.toko_id_akun', '=', 'akun.id_akun')
                                ->join('order_', 'order_.order_id_toko', '=', 'toko.id_toko')
                                ->where([
                                    'order_.id_order'           => $id_order,
                                    'order_.order_id_order'     => $order_id,
                                    'order_.order_id_buyer'     => $id_user,
                                    'order_.has_sent'           => 1,
                                    'order_.transaction_status' => 'settlement'
                                ])->update([
                                    'order_.has_received'       => 1,
                                    'epay.saldo'                => DB::raw('epay.saldo + (order_.harga_barang * order_.quantity)')
                                ]);
    }

    /**
     * Insert order ketika transaksi status dibuat
     * @param  int $id_user
     * @param  string $order_id
     * @param  string $transaction_status
     * @return boolean
     */
    public function insertOrder($id_user, $order_id, $transaction_status) {
        // ambil keranjang
        $carts = DB::table('keranjang')->select([
            'keranjang_id_barang', 'quantity', 'message', 'toko.id_toko', 'barang.harga'
        ])->join('barang', 'barang.id_barang', '=', 'keranjang.keranjang_id_barang')
          ->join('toko', 'toko.id_toko', '=', 'barang.barang_id_toko')
          ->where([
            'keranjang_id_akun'   => $id_user,
            'keranjang_is_active' => 1
        ])->get();

        // ambil alamat
        $address = DB::table('alamat')->select([
            'alamat_id_kota', 'nama_alamat', 'nama_penerima', 'no_hp_penerima', 'alamat_lengkap'
        ])->where([
            'alamat_id_akun'    => $id_user,
            'alamat_utama'      => 1
        ])->first();

        $orders = collect($carts)->map(function($item) use ($id_user, $address, $order_id, $transaction_status) {
            return [
                "order_id_order"      => $order_id,
                "order_id_barang"     => $item->keranjang_id_barang,
                "harga_barang"        => $item->harga,
                "order_id_buyer"      => $id_user,
                "quantity"            => $item->quantity,
                "message"             => $item->message,
                "order_id_toko"       => $item->id_toko,
                "order_id_kota"       => $address->alamat_id_kota,
                "nama_alamat"         => $address->nama_alamat,
                "nama_penerima"       => $address->nama_penerima,
                "no_hp_penerima"      => $address->no_hp_penerima,
                "alamat_lengkap"      => $address->alamat_lengkap,
                "transaction_status"  => $transaction_status,
                "has_sent"            => 0,
                "created_at"          => now(),
                "updated_at"          => now()
            ];
        })->toArray();

        return OrderModel::insert($orders);
    }
}
