<?php

namespace App\Http\Controllers\Api\Seller\Shop\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use App\Models\Seller\Product\ProductModel;

use App\Http\Requests\Seller\Shop\Product\ProductArchiveRequestUpdate;
use App\Http\Requests\Seller\Shop\Product\ProductUnarchiveRequestUpdate;

use App\Http\Requests\Seller\Shop\Product\Crud\ProductRequestInsert;

class ProductController extends Controller {

    /**
     * Attribute untuk menyimpan ProductModel
     * @var App\Models\Seller\Product\ProductModel
     */
    private $productModel;

    /**
     * Attribute untuk menyimpan user
     * @var App\Auth\Guard\TokenGuard
     */
    private $user;

    public function __construct() {
        $this->productModel = new ProductModel;
        $this->user = auth("user")->decodeToken("name");
    }

    /**
     * Ambil semua product
     * @return json
     */
    public function getProduct() {
        if ($this->user->exists()) {
            $user = $this->user;

            $products   = $this->productModel->getProduct($user->id());

            return json([
                "message"     => "Barang berhasil diambil",
                "total"       => count($products),
                "products"    => $products
            ]);
        }

        return error401();
    }

    /**
     * Ambil product pada halaman ke - {$page}
     * @param  int $page
     * @return json
     */
    public function getProductAtPage($page) {
        if ($this->user->exists()) {
            $user = $this->user;
            $take = 10;

            $products = $this->productModel->getProductAtPage($user->id(), $page, $take);

            // ceil untuk menghindari desimal
            $availablePage     = ceil($products->total/$take);
            $next              = $page < $availablePage ? $page + 1 : null;
            $previous          = $page > 1 && !($page > $availablePage) ? $page - 1 : null;

            $currentPageUrl    = route('shop-product-pagination', ['page' => $page]);
            $lastPageUrl       = route('shop-product-pagination', ['page' => $availablePage]);
            $firstPageUrl      = route('shop-product-pagination', ['page' => 1]);

            $nextPageUrl       = $next == null     ? null : route('shop-product-pagination', ['page' => $next]);
            $previousPageUrl   = $previous == null ? null : route('shop-product-pagination', ['page' => $previous]);

            $response = [
                "message"           => count($products->data) == 0 ? "Alamat tidak ditemukan" : "Alamat berhasil ditemukan",
                "available_page"    => $availablePage,
                "page"              => [
                    "current"       => $currentPageUrl,
                    "next"          => $nextPageUrl,
                    "previous"      => $previousPageUrl,
                    "first"         => $firstPageUrl,
                    "last"          => $lastPageUrl
                ],
                "total"             => [
                    "all"           => $products->total,
                    "this_page"     => count($products->data)
                ],
                "products"          => $products->data,
            ];

            return json($response);
        }

        return error401();
    }

    /**
     * Fungsi untuk menambah produk
     * @param  ProductRequestInsert $request
     * @return json
     */
    public function insertProduct(ProductRequestInsert $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            if ($request->product_stock < $request->product_minimum_order) {
                return error422(null, "product_stock", "Stok produk pertama tidak boleh kurang dari pesanan minimum");
            }

            if ($request->product_discount > $request->product_price) {
                return error422(null, "product_discount", "Diskon tidak boleh lebih dari harga asli");
            }

            $productName              = $request->product_name;
            $productDescription       = $request->product_description;
            $productImages            = $request->file('product_images');
            $productPrice             = $request->product_price;
            $productDiscount          = $request->product_discount;
            $productSlug              = Str::slug($productName.'-'.date('dmYHis').$user->id().Str::random(5));
            $productStock             = $request->product_stock;
            $productWeight            = $request->product_weight;
            $productCondition         = $request->product_condition;
            $productMinimumOrder      = $request->product_minimum_order;
            $productInsurance         = $request->product_insurance;
            $productPreOrder          = $request->product_preorder;
            $productSizeLong          = $request->product_size_long;
            $productSizeWide          = $request->product_size_wide;
            $productSizeHeight        = $request->product_size_height;

            $imageNames = $this->handleUploadedImage($productImages);
            if (!$imageNames) {
                return error500(null, "product_images", "Terjadi masalah saat menyimpan foto produk");
            }

            $inserted = $this->productModel->insertProduct(
                $user->id(),
                $user->toko()->id(),
                $productName,
                $productDescription,
                $productSlug,
                $imageNames,
                $productPrice,
                $productDiscount,
                $productStock,
                $productWeight,
                $productCondition,
                $productMinimumOrder,
                $productInsurance,
                $productPreOrder,
                $productSizeLong,
                $productSizeWide,
                $productSizeHeight
            );

            if ($inserted) {
                return json([
                    "message"     => "Tambah produk berhasil",
                    "date"        => date("d-m-Y H:i")
                ]);
            }

            return error500(null, "server", "Terjadi masalah saat menambah produk, silakan refresh dan coba lagi");
        }

        return error401();
    }

    /**
     * Handle image yang diupload
     * @param  array  $images
     * @return mixed boolean|array
     */
    private function handleUploadedImage(array $images) {
        $path       = public_path('/assets/images/products/');
        $imagesName = [];
        foreach ($images as $image) {

            $extension  = $image->extension();
            $name       = $this->randomImageName(45, $extension);

            array_push($imagesName, $name);

            if (!$image->move($path, $name)) {
                return false;
            }
        }

        return $imagesName;
    }

    /**
     * Memberikan string random untuk image
     * @param  integer $count
     * @param  string  $extension
     * @return string
     */
    private function randomImageName($count = 30, $extension){
        return Str::random($count).$this->user->id().date("_d_m_Y_H_i_s.").$extension;
    }

    /**
     * Fungsi untuk mengaktifkan produk
     * @param  App\Http\Requests\Seller\Shop\Product\ProductUnarchiveRequestUpdate $request
     * @return json
     */
    public function unarchiveProduct(ProductUnarchiveRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            $row = $this->productModel->unarchiveProduct($user->id(), $request->id_barang);

            try {
                $response = [
                    "message"     => $row <= 0 ? "Tidak produk yang diaktifkan" : "Produk berhasil diaktifkan kembali",
                    "date"        => date("d-m-Y H:i")
                ];

                return json($response);
            } catch (\Exception $e) {}

            return error500(null, null, "Terjadi masalah saat mengaktifkan produk");
        }

        return error401();
    }

    /**
     * Fungsi untuk mengarsipkan produk, sebelum benar benar dihapus
     * @param  App\Http\Requests\Seller\Shop\Product\ProductArchiveRequestUpdate $request
     * @return json
     */
    public function archiveProduct(ProductArchiveRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            $row = $this->productModel->archiveProduct($user->id(), $request->id_barang);

            try {
                $response = [
                    "message"     => $row <= 0 ? "Tidak produk yang diarsipkan" : "Produk berhasil diarsipkan",
                    "date"        => date("d-m-Y H:i")
                ];

                return json($response);
            } catch (\Exception $e) {}

            return error500(null, null, "Terjadi masalah saat mengarsipkan produk");
        }

        return error401();
    }
}
