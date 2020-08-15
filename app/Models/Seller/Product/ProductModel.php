<?php

namespace App\Models\Seller\Product;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

use App\Models\Seller\Product\ProductModel;

class ProductModel extends Model {
    protected $table = 'barang';

    /**
     * Ambil semua product berdasarkan id
     * @param  int $id_user
     * @return array
     */
    public function getProduct($id_user) {
        return ProductModel::select([
            'barang.id_barang', 'barang.nama_barang', 'barang.slug', 'barang.informasi_barang', 'barang.stok_barang',
            'barang.terjual', 'barang.barang_aktif'
        ])->join('toko', 'toko.id_toko', '=', 'barang.barang_id_toko')
          ->where([
             'toko.toko_id_akun' => $id_user,
             'barang.barang_aktif' => 1
          ])->get();
    }

    /**
     * Fungsi untuk mengarsipkan produk
     * @param  int $id_user
     * @param  int $id_product
     * @return int mengembalikan banyaknya row yang terupdate
     */
    public function archiveProduct($id_user, $id_product) {
        return ProductModel::join('toko', 'toko.id_toko', '=', 'barang.barang_id_toko')->where([
            'toko.toko_id_akun'     => $id_user,
            'barang.id_barang'      => $id_product
        ])->update([
            'barang.barang_aktif'   => 0
        ]);
    }

    /**
     * Fungsi untuk mengaktifkan produk kembali
     * @param  int $id_user
     * @param  int $id_product
     * @return int mengembalikan banyaknya row yang terupdate
     */
    public function unarchiveProduct($id_user, $id_product) {
        return ProductModel::join('toko', 'toko.id_toko', '=', 'barang.barang_id_toko')->where([
            'toko.toko_id_akun'     => $id_user,
            'barang.id_barang'      => $id_product
        ])->update([
            'barang.barang_aktif'   => 1
        ]);
    }

    /**
     * Fungsi model untuk menambah produk
     * @param  int $id_user
     * @param  int $id_toko
     * @param  string $product_name
     * @param  string $product_description
     * @param  array  $product_images
     * @param  int $product_price
     * @param  int $product_stock
     * @param  int $product_weight
     * @param  int $product_condition
     * @param  int $product_minimum_order
     * @param  int $product_insurance
     * @param  int $product_preorder
     * @param  int $product_size_long
     * @param  int $product_size_wide
     * @param  int $product_size_height
     * @return int mengembalikan banyaknya row yang terinsert
     */
    public function insertProduct($id_user, $id_toko, $product_name, $product_description, $product_slug, array $product_images, $product_price, $product_discount, $product_stock, $product_weight, $product_condition, $product_minimum_order, $product_insurance, $product_preorder, $product_size_long, $product_size_wide, $product_size_height) {
        // ambil id
        $id = ProductModel::insertGetId([
            'barang_id_toko'        => $id_toko,
            'nama_barang'           => $product_name,
            'informasi_barang'      => $product_description,
            'slug'                  => $product_slug,
            'harga'                 => $product_price,
            'diskon'                => $product_discount,
            'stok_barang'           => $product_stock,
            'berat_barang'          => $product_weight,
            'kondisi'               => $product_condition == 1 ? 'Bekas' : 'Baru',
            'terjual'               => 0,
            'pesanan_minimum'       => $product_minimum_order,
            'asuransi'              => $product_insurance == 1 ? 'Optional' : 'Ya',
            'preorder'              => $product_preorder,
            'lebar_barang'          => $product_size_wide,
            'panjang_barang'        => $product_size_long,
            'tinggi_barang'         => $product_size_height,
            'barang_aktif'          => 1,
            'status_barang'         => 1,
            'created_at'            => date('Y-m-d H:i:s'),
            'updated_at'            => date('Y-m-d H:i:s')
        ]);

        // masukkan ke images
        $images = collect($product_images)->map(function($name) use($id) {
            return ['foto_barang_id_barang' => $id, 'foto_url' => $name];
        })->all();

        $row = DB::table('foto_barang')->insert($images);

        return $id > 0 && $row > 0;
    }

    /**
     * Ambil product pada halaman ke - {$page}
     * @param  int $id_user
     * @param  int $page
     * @param  int $take
     * @return object
     */
    public function getProductAtPage($id_user, $page, $take) {
        $query = ProductModel::query();
        $query->select([
            'barang.id_barang', 'barang.nama_barang', 'barang.slug', 'barang.informasi_barang', 'barang.stok_barang',
            'barang.terjual', 'barang.barang_aktif'
        ])->join('toko', 'toko.id_toko', '=', 'barang.barang_id_toko')
          ->where([
             'toko.toko_id_akun' => $id_user,
             'barang.barang_aktif' => 1
          ])->get();

        // example
        // page = 2, take = 10
        // to = 2 * 10 = 20
        // from = 20 - 10
        $to       = $page * $take;
        $from     = $to - $take;

        $total    = $query->count();
        $address  = $query->take($take)->skip($from)->get();

        $data = new \stdClass;
        $data->total = $total;
        $data->data  = $address;

        return $data;
    }
}
