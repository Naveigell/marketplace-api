<?php

namespace App\Models\Seller\Order;

use Illuminate\Database\Eloquent\Model;
use App\Models\Seller\Order\OrderModel;

class OrderModel extends Model {
    protected $table = 'order_';

    /**
     * Ambil semua order yang masuk berdasarkan id toko
     * @param  int $id_toko
     * @return array
     */
    public function getOrderByShopId($id_toko) {
        return OrderModel::select([
            'order_.order_id_order', 'order_.quantity', 'order_.message', 'order_.nama_alamat',
            'order_.nama_penerima', 'order_.no_hp_penerima', 'order_.alamat_lengkap', 'order_.transaction_status',

            'kota.nama_provinsi', 'kota.tipe', 'kota.nama_kota', 'kota.postal_code',

            'barang.nama_barang', 'barang.slug', 'barang.harga', 'barang.diskon', 'barang.stok_barang',
            'barang.berat_barang'
        ])
        ->join('barang', 'barang.id_barang', '=', 'order_.order_id_barang')
        ->join('kota', 'kota.id_kota', '=', 'order_.order_id_kota')

        ->where('order_id_toko', $id_toko)->get();
    }

    public function cancelOrderBySeller($id_order, $id_toko, $order_id) {
        return OrderModel::where([
            'id_order'            => $id_order,
            'order_id_toko'       => $id_toko,
            'order_id_order'      => $order_id
        ])->update([
            'transaction_status'  => 'cancel'
        ]);
    }
}
