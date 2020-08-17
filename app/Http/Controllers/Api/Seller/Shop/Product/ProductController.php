<?php

namespace App\Http\Controllers\Api\Seller\Shop\Product;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Config;

use App\Models\Seller\Product\ProductModel;

use App\Http\Requests\Seller\Shop\Product\ProductArchiveRequestUpdate;
use App\Http\Requests\Seller\Shop\Product\ProductUnarchiveRequestUpdate;

use App\Http\Requests\Seller\Shop\Product\Crud\ProductRequestInsert;
use App\Http\Requests\Seller\Shop\Product\Crud\ProductRequestUpdate;

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
     * Ambil product pada halaman ke - n
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
     * Fungsi untuk menghapus sementara produk
     * @param  Request $request 
     * @return json
     */
    public function softDeleteProduct(Request $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            try {
                $row = $this->productModel->softDeleteProduct($user->id(), $request->id_barang);

                $response = [
                    "message"     => $row > 0 ? "Produk berhasil dihapus" : "Tidak ada produk yang dihapus",
                    "date"        => date("d-m-Y H:i")
                ];
                return json($response);
            } catch (\Exception $e) {}

            return error500(null, "product", "Terjadi masalah saat menghapus barang");
        }

        return error401();
    }

    /**
     * Fungsi untuk mengupdate barang seller
     * @param  App\Http\Requests\Seller\Shop\Product\Crud\ProductRequestUpdate $request
     * @return json
     */
    public function updateProduct(ProductRequestUpdate $request, $id) {
        if ($this->user->exists()) {
            $user = $this->user;

            // untuk temporary
            $latestImages           = $this->productModel->getProdutImages($user->id(), $id);
            $latestImageCollections = collect($latestImages)->map(function($array){
                return $array->foto_url;
            });

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
            $productStock             = $request->product_stock;
            $productWeight            = $request->product_weight;
            $productCondition         = $request->product_condition;
            $productMinimumOrder      = $request->product_minimum_order;
            $productInsurance         = $request->product_insurance;
            $productPreOrder          = $request->product_preorder;
            $productSizeLong          = $request->product_size_long;
            $productSizeWide          = $request->product_size_wide;
            $productSizeHeight        = $request->product_size_height;

            $newImageCollections = $this->handleUploadedImage($productImages);
            if (!$newImageCollections) {
                return error500(null, "product_images", "Terjadi masalah saat menyimpan foto produk");
            }

            $updated = $this->productModel->updateProduct(
                $user->id(),
                $id,
                $user->toko()->id(),
                $productName,
                $productDescription,
                (object) $latestImageCollections,
                (object) $newImageCollections,
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

            if ($updated) {
                $this->deleteProductImages((object) $latestImageCollections);

                return json([
                    "message"     => "Edit produk berhasil",
                    "date"        => date("d-m-Y H:i")
                ]);
            }

            return error500(null, "product", "Terjadi masalah saat mengupdate product");
        }

        return error401();
    }

    /**
     * Fungsi untuk mendelete image di assets
     * @param  object $images
     * @return void
     */
    public function deleteProductImages(object $images) {
        foreach ($images as $image) {
            $path = public_path("/assets/images/products/$image");
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    /**
     * Ambil barang berdasarkan id
     * @param  int $id
     * @return json
     */
    public function getProductById($id) {
        if ($this->user->exists()) {
            $user = $this->user;

            $products = $this->productModel->getProductById($id, $user->toko()->id());

            if (count($products) > 0) {
                $barang = new \stdClass;
                $toko = new \stdClass;

                $barang->id_barang                  = $products[0]->id_barang;
                $barang->nama_barang                = $products[0]->nama_barang;
                $barang->slug                       = $products[0]->slug;
                $barang->deskripsi                  = $products[0]->informasi_barang;
                $barang->costs                      = new \stdClass;
                $barang->costs->harga               = $products[0]->harga;
                $barang->costs->diskon              = $products[0]->diskon;
                $barang->stok_barang                = $products[0]->stok_barang;
                $barang->berat_barang               = $products[0]->berat_barang;
                $barang->kondisi                    = $products[0]->kondisi;
                $barang->pesanan_minimum            = $products[0]->pesanan_minimum;
                $barang->size                       = new \stdClass;
                $barang->size->lebar_barang         = $products[0]->lebar_barang;
                $barang->size->panjang_barang       = $products[0]->panjang_barang;
                $barang->size->tinggi_barang        = $products[0]->tinggi_barang;
                $barang->foto_url                   = [config('app.assets_url_images_barang')."/".$products[0]->foto_url];

                if (count($products) > 1) {
                    for ($i = 1; $i < count($products); $i++) {
                        array_push($barang->foto_url, config('app.assets_url_images_barang')."/".$products[$i]->foto_url);
                    }
                }

                return json([$barang->slug => $barang]);
            }

            return error404(null, 'barang', 'Barang yang dicari tidak ditemukan');
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

            try {
                $row = $this->productModel->unarchiveProduct($user->id(), $request->id_barang);

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

            try {
                $row = $this->productModel->archiveProduct($user->id(), $request->id_barang);

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
