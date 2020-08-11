<?php

namespace App\Http\Controllers\Api\Buyer\Cart;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Buyer\Product\ProductModel;
use App\Models\Buyer\Cart\CartModel;

use App\Http\Requests\Buyer\Cart\CartRequestUpdate;
use App\Http\Requests\Buyer\Cart\CartRequestDelete;
use App\Http\Requests\Buyer\Cart\CartRequestToggle;

use Illuminate\Support\Facades\DB;

use App\Auth\User;

use Config;
use Carbon\Carbon;

class CartController extends Controller {

    private $productModel, $cartModel;

    /**
     * Attribute untuk menyimpan guard user
     *
     * @var App\Auth\Guard\TokenGuard | null
     */
    private $user;

    public function __construct() {
        $this->productModel = new ProductModel;
        $this->cartModel    = new CartModel;

        $this->user = auth('user')->decodeToken('name');
    }

    /**
     * Fungsi untuk mengambil keranjang dari user berdasarkan id user
     *
     * @param  Request $request
     *
     * @return json
     */
    public function get(Request $request){
        if ($this->user->exists()) {

            $user = $this->user;
            $array = $this->cartModel->getCart($user->id());

            $temp = [];
            $data = [];

            // tambahkan object berdasarkan nama toko
            foreach ($array as $arr) {
                if (!isset($temp[$arr->nama_toko])) {
                    $temp[$arr->nama_toko] = [$arr];
                } else {
                    array_push($temp[$arr->nama_toko], $arr);
                }
            }

            // looping nama toko & barang agar ditampilkan sesuai nama toko
            foreach ($temp as $t) {
                $toko   = new \stdClass;

                $toko->id                   = null;
                $toko->nama_toko            = null;
                $toko->slug                 = null;
                $toko->foto_url             = null;
                $toko->pesanan              = [];

                // looping barang
                foreach ($t as $b) {
                    $barang = new \stdClass;

                    $toko->nama_toko            = $b->nama_toko;
                    $toko->slug                 = $b->slug_toko;
                    $toko->foto_url             = config('app.assets_url_images_toko')."/".$b->toko_foto_url;

                    $barang->id                 = $b->id_barang;
                    $barang->nama_barang        = $b->nama_barang;
                    $barang->slug               = $b->slug;
                    $barang->deskripsi          = $b->informasi_barang;
                    $barang->harga              = $b->harga;
                    $barang->stok               = $b->stok;
                    $barang->diskon             = $b->diskon;
                    $barang->pesanan_minimum    = $b->pesanan_minimum;
                    $barang->aktif              = $b->keranjang_is_active;
                    $barang->foto_url           = [config('app.assets_url_images_barang')."/".$b->foto_url];

                    array_push($toko->pesanan, $barang);
                }

                array_push($data, $toko);
            }

            return json(["message" => "Keranjang berhasil didapatkan", "carts" => $data]);
        }

        return error(null, ["auth" => ["User belum login"]], 401);
    }

    /**
     * Fungsi untuk menghapus keranjang berdasarkan id user
     *
     * @param  CartRequestDelete $request
     *
     * @return json
     */
    public function delete(CartRequestDelete $request) {
        if ($this->user->exists()) {
            $user             = $this->user;
            $itemID           = $request->id;

            if (!is_array($itemID) && $itemID < 1) {
                return error(null, ["id" => ["Id barang minimum 1"]], 422);
            }

            $row = $this->cartModel->deleteCart($user->id(), $itemID);

            try {
                $response = [
                    "cart" => [
                        "message"     => "Tidak ada keranjang yang dihapus",
                        "details"     => [
                            "id"      => $itemID,
                            "jumlah"  => is_array($itemID) ? count($itemID) : 1
                        ]
                    ]
                ];

                if ($row > 0) {
                    $response["cart"]["message"] = "Keranjang berhasil dihapus";
                }

                return json($response);
            } catch (\Exception $e) {}

            return error(null, ["server" => ["Terjadi masalah pada server"]], 500);
        }

        return error(null, ["auth" => ["User belum login"]], 401);
    }

    /**
     * Fungsi toggle untuk membuat keranjang aktif atau unactive
     *
     * @param  App\Http\Requests\Buyer\Cart\CartRequestToggle $request
     *
     * @return void
     */
    public function toggle(CartRequestToggle $request) {
        if ($this->user->exists()) {
            $user             = $this->user;
            $itemID           = $request->id;

            if (!is_array($itemID) && $itemID < 1) {
                return error(null, ["id" => ["Id barang minimum 1"]], 422);
            }

            // toggle ke model
            $row = $this->cartModel->toggle($user->id(), $itemID, $request->toggle);

            $response = [
                "cart" => [
                    "message"     => "Tidak ada keranjang yang diaktifkan atau nonaktifkan",
                    "details"     => [
                        "id"      => $itemID,
                        "jumlah"  => is_array($itemID) ? count($itemID) : 1
                    ]
                ]
            ];

            if ($row > 0) {
                $response["cart"]["message"] = "Barang dikeranjang berhasil di ".($request->toggle ? "aktifkan" : "nonaktifkan");
            }

            return json($response);
        }

        return error(null, ["auth" => ["User belum login"]], 401);
    }

    /**
     * Fungsi error order agar membuat kode lebih clean
     * @param  string $type
     * @param  string $name
     * @param  string $message
     * @return json
     */
    private function errorOrder($type, $name, $message) {
        $details  = ["type" => $type, "name" => $name];
        $order    = [$message];

        return error($details, ["order" => $order], 422);
    }

    /**
     * Fungsi untuk update keranjang berdasarkan id user
     *
     * @param  CartRequestUpdate $request
     *
     * @return json
     */
    public function update(CartRequestUpdate $request){
        if ($this->user->exists()) {
            $user             = $this->user;
            $itemID           = $request->id_barang;
            $quantity         = $request->quantity;

            $item             = $this->productModel->getProductBuyMinimumAndStockByID($itemID);

            if (count($item) > 0) {
                $item = $item[0];

                if ($quantity > $item->stok_barang) {
                    return $this->errorOrder("gt", "Greater than", "Pesanan melebihi stok barang");
                }
                else if ($quantity < $item->pesanan_minimum) {
                    return $this->errorOrder("lt", "Less than", "Pesanan tidak memenuhi pesanan minimum");
                }
                else if($user->id() == $item->id_akun) {
                    return error(null, ["order" => ["Tidak bisa memesan barang sendiri"]], 422);
                }

                $row = $this->cartModel->updateCart($user->id(), $itemID, $quantity);

                $response = [
                    "cart" => [
                        // nothing happened
                        "message" => "Tidak ada keranjang yang ditambah atau diubah",
                        "details" => [
                            "id_barang" => $itemID,
                            "quantity"  => $quantity
                        ]
                    ]
                ];

                // has updated
                if (!$row->wasRecentlyCreated && $row->wasChanged()) {
                    $response["cart"]["message"] = "Update keranjang berhasil";
                }
                // has created
                else if($row->wasRecentlyCreated) {
                    $response["cart"]["message"] = "Tambah keranjang berhasil";
                }

                return json($response);
            }

            return error(null, ["item" => ["Barang tidak ditemukan"]], 404);
        }

        return error(null, ["auth" => ["User belum login"]], 401);
    }
}
