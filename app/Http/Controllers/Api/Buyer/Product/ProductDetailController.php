<?php

namespace App\Http\Controllers\Api\Buyer\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Config;

use App\Models\Buyer\Product\ProductModel;

class ProductDetailController extends Controller {

    private $productModel;

    public function __construct() {
        $this->productModel = new ProductModel;
    }

    public function getProductDetail($slug) {
        $array = $this->productModel->getProductDetail($slug);
        $data = [];

        if (count($array) > 0) {
            $barang = new \stdClass;
            $toko = new \stdClass;

            $barang->id_barang            = $array[0]->id_barang;
            $barang->nama_barang          = $array[0]->nama_barang;
            $barang->slug                 = $array[0]->slug;
            $barang->deskripsi            = $array[0]->informasi_barang;
            $barang->harga                = $array[0]->harga;
            $barang->stok_barang          = $array[0]->stok_barang;
            $barang->diskon               = $array[0]->diskon;
            $barang->berat_barang         = $array[0]->berat_barang;
            $barang->kondisi              = $array[0]->kondisi;
            $barang->terjual              = $array[0]->terjual;
            $barang->pesanan_minimum      = $array[0]->pesanan_minimum;
            $barang->lebar_barang         = $array[0]->lebar_barang;
            $barang->panjang_barang       = $array[0]->panjang_barang;
            $barang->tinggi_barang        = $array[0]->tinggi_barang;
            $barang->foto_url             = [config('app.assets_url_images_barang')."/".$array[0]->foto_url];

            $toko->id_toko                = $array[0]->id_toko;
            $toko->nama_toko              = $array[0]->nama_toko;
            $toko->slug_toko              = $array[0]->slug_toko;
            $toko->foto_url               = config('app.assets_url_images_toko')."/".$array[0]->toko_foto_url;

            $toko->lokasi = new \stdClass;
            $toko->lokasi->label_lokasi   = $array[0]->label_lokasi;
            $toko->lokasi->kode_pos       = $array[0]->kode_pos;
            $toko->lokasi->fax            = $array[0]->fax;
            $toko->lokasi->nama_provinsi  = $array[0]->nama_provinsi;
            $toko->lokasi->tipe           = $array[0]->tipe;
            $toko->lokasi->nama_kota      = $array[0]->nama_kota;
            $toko->lokasi->postal_code    = $array[0]->postal_code;

            if (count($array) > 1) {
                for ($i = 1; $i < count($array); $i++) {
                    array_push($barang->foto_url, config('app.assets_url_images_barang')."/".$array[$i]->foto_url);
                }
            }

            return json(["item" => ["barang" => $barang, "toko" => $toko]]);
        }

        return error(["type" => "Not Found"], ["item" => ["Barang tidak ditemukan"]], 404);
    }
}
