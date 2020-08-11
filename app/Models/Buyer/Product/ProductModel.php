<?php

namespace App\Models\Buyer\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Buyer\Product\ProductModel;

class ProductModel extends Model {

    protected $table = "barang";

    public function getProductDetail($slug) {
        DB::statement("SET sql_mode=''");
        return ProductModel::select(["*"])
                            ->join("toko", "barang.barang_id_toko", "=", "toko.id_toko")
                            ->join("lokasi", "toko.id_toko", "=", "lokasi.lokasi_id_toko")
                            ->join("kota", "kota.id_kota", "=", "toko.kota")
                            ->join("foto_barang", "barang.id_barang", "=", "foto_barang.foto_barang_id_barang")
                            ->where("slug", $slug)->get();
    }

    public function getProductBuyMinimumAndStockByID($id){
        return ProductModel::select(["pesanan_minimum", "stok_barang", "id_akun"])->join("toko", "toko.id_toko", "=", "barang.barang_id_toko")
                                                                                  ->join("akun", "akun.id_akun", "=", "toko.toko_id_akun")
                                                                                  ->where("id_barang", $id)->get();
    }
}
