<?php

namespace App\Models\Buyer\Home;

use Illuminate\Database\Eloquent\Model;
use App\Models\Buyer\Home\RecommendationModel;
use Illuminate\Support\Facades\DB;

class RecommendationModel extends Model {
    protected $table = "barang";

    public function getBarang(){
        DB::statement("SET sql_mode = '' ");

        return RecommendationModel::select([
             "barang.id_barang", "barang.nama_barang", "barang.harga", "barang.diskon", "barang.slug", "foto_barang.foto_url",
             "toko.alamat_toko", "toko.nama_toko", "kota.tipe", "kota.nama_kota"
        ])
        ->join("foto_barang", "barang.id_barang", "=", "foto_barang.foto_barang_id_barang")
        ->join("toko", "barang.barang_id_toko", "=", "toko.id_toko")
        ->leftJoin("kota", "toko.kota", "=", "kota.id_kota")
        ->where("barang.barang_aktif", "=", "1")
        ->groupBy("barang.id_barang")
        ->get();
    }
}
