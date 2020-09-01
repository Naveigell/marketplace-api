<?php

namespace App\Models\Buyer\Cart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Buyer\Cart\CartModel;

class CartModel extends Model {
    protected $table = "keranjang";
    protected $fillable = ["keranjang_id_akun", "keranjang_id_barang", "quantity"];
    protected $primaryKey = 'id_keranjang';

    /**
     * Update keranjang
     * @param  int $id_user
     * @param  int $id_barang
     * @param  int $quantity
     * @return CartModel
     */
    public function updateCart($id_user, $id_barang, $quantity) {

        $instance = CartModel::updateOrCreate(
            ["keranjang_id_akun" => $id_user, "keranjang_id_barang" => $id_barang],
            ["quantity" => $quantity, "created_at" => Carbon::now(), "updated_at" => Carbon::now()]
        );
        $instance->save();

        return $instance;
    }

    /**
     * Fungsi untuk toggle atau merubah keaktifan keranjang
     * @param  string $id_user
     * @param  string $id_barang
     * @param  string $active
     * @return boolean
     */
    public function toggle($id_user, $id_barang, $active) {
        // bukan array
        if (!is_array($id_barang)) {
            return CartModel::where([
               "keranjang_id_akun"    => $id_user,
               "keranjang_id_barang"  => $id_barang
            ])->update(["keranjang_is_active" => $active]);
        }

        return CartModel::where("keranjang_id_akun", $id_user)->whereIn("keranjang_id_barang", $id_barang)->update(["keranjang_is_active" => $active]);
    }

    /**
     * Hapus keranjang
     * @param  int $id_user
     * @param  int $id_barang
     * @return int
     */
    public function deleteCart($id_user, $id_barang) {

        if (!is_array($id_barang)) {
            return CartModel::where([
               "keranjang_id_akun"    => $id_user,
               "keranjang_id_barang"  => $id_barang
            ])->delete();
        }

        return CartModel::where("keranjang_id_akun", $id_user)->whereIn("keranjang_id_barang", $id_barang)->delete();
    }

    public function getCartForPayment($id_user) {
        DB::statement("SET sql_mode = '' ");
        return CartModel::select([
            "barang.id_barang", "barang.harga", "barang.diskon", "keranjang.quantity", "barang.nama_barang"
        ])->join("barang", "barang.id_barang", "=", "keranjang.keranjang_id_barang")
          ->join("toko", "toko.id_toko", "=", "barang.barang_id_toko")
          ->join("foto_barang", "foto_barang.foto_barang_id_barang", "=", "barang.id_barang")
          ->groupBy('foto_barang.foto_barang_id_barang')
          ->where(['keranjang_id_akun' => $id_user, 'keranjang.keranjang_is_active' => 1])
          ->get();
    }

    /**
     * Ambil keranjang
     * @param  int $id_user
     * @return array
     */
    public function getCart($id_user) {
        DB::statement("SET sql_mode = '' ");
        return CartModel::select([
            "keranjang.id_keranjang", "keranjang.quantity", "keranjang.keranjang_is_active", "barang.id_barang", "barang.nama_barang",
            "barang.slug", "barang.informasi_barang", "barang.harga", "barang.stok_barang", "barang.diskon",
            "barang.pesanan_minimum", "toko.id_toko", "toko.nama_toko", "toko.slug_toko", "toko.toko_foto_url", "foto_barang.foto_url"
        ])->join("barang", "barang.id_barang", "=", "keranjang.keranjang_id_barang")
          ->join("toko", "toko.id_toko", "=", "barang.barang_id_toko")
          ->join("foto_barang", "foto_barang.foto_barang_id_barang", "=", "barang.id_barang")
          ->groupBy('foto_barang.foto_barang_id_barang')
          ->where('keranjang_id_akun', $id_user)
          ->get();
    }
}
