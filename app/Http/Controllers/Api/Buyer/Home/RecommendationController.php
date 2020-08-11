<?php

namespace App\Http\Controllers\Api\Buyer\Home;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Buyer\Home\RecommendationModel;

class RecommendationController extends Controller {

    private $recommendationModel;

    public function __construct() {
        $this->recommendationModel = new RecommendationModel();
    }

    public function getRecommendation(){
        $array = $this->recommendationModel->getBarang();
        $data = [];

        foreach($array as $arr) {
            $barang = new \stdClass;
            $toko   = new \stdClass;

            $barang->id_barang          = $arr->id_barang;
            $barang->nama_barang        = $arr->nama_barang;
            $barang->harga              = $arr->harga;
            $barang->diskon             = $arr->diskon;
            $barang->slug               = $arr->slug;
            $barang->nama_barang        = $arr->nama_barang;

            $toko->lokasi               = new \stdClass;
            $toko->lokasi->alamat_toko  = $arr->alamat_toko;
            $toko->lokasi->tipe         = $arr->tipe;
            $toko->lokasi->nama_kota    = $arr->nama_kota;
            $toko->nama_toko            = $arr->nama_toko;
            $toko->barang               = $barang;

            array_push($data, ["toko" => $toko]);
        }

        return json(["items" => $data]);
    }
}
