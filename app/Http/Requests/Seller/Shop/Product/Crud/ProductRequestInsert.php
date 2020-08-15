<?php

namespace App\Http\Requests\Seller\Shop\Product\Crud;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequestInsert extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize() {
        return true;
    }

    /**
     * Messages untuk validate rules
     * @return array
     */
    public function messages() {
        return [
            'product_name.required'                   => 'Nama produk harus diisi',
            'product_name.min'                        => 'Panjang nama produk minimal 6',
            'product_name.max'                        => 'Panjang nama produk maksimal 100',

            'product_description.required'            => 'Deskripsi produk harus diisi',
            'product_description.min'                 => 'Panjang deskripsi produk minimal 10',
            'product_description.max'                 => 'Panjang deskripsi produk maksimal 2.000',

            'product_images.required'                 => 'Foto produk harus diisi',
            'product_images.array'                    => 'Foto produk hanyak bisa berupa array',
            'product_images.min'                      => 'Banyak foto yang dipilih minimal 1',

            'product_images.*.required'               => 'Foto produk harus diisi',
            'product_images.*.mimes'                  => 'Foto produk hanyak bisa berupa png, jpg atau jpeg',
            'product_images.*.max'                    => 'Ukuran foto maksimal 10 MB',

            'product_price.required'                  => 'Harga produk harus diisi',
            'product_price.integer'                   => 'Harga produk harus berupa angka',
            'product_price.min'                       => 'Harga produk minimal 10',
            'product_price.max'                       => 'Harga produk minimal 1.000.000.000',

            'product_discount.integer'                => 'Diskon produk harus berupa angka',
            'product_discount.min'                    => 'Diskon produk minimal 10',
            'product_discount.max'                    => 'Diskon produk minimal 1.000.000.000',

            'product_stock.required'                  => 'Stok produk harus diisi',
            'product_stock.integer'                   => 'Stok produk harus berupa angka',
            'product_stock.min'                       => 'Stok produk minimal 1',
            'product_stock.max'                       => 'Stok produk minimal 999.999',

            'product_weight.required'                 => 'Berat produk harus diisi',
            'product_weight.integer'                  => 'Berat produk harus berupa angka',
            'product_weight.min'                      => 'Berat produk minimal 0',
            'product_weight.max'                      => 'Berat produk minimal 999.999',

            'product_condition.required'              => 'Kondisi produk harus diisi',
            'product_condition.integer'               => 'Kondisi produk harus berupa angka',
            'product_condition.between'               => 'Kondisi produk minimal antara 1 dan 2',

            'product_minimum_order.required'          => 'Pesanan minimum produk harus diisi',
            'product_minimum_order.integer'           => 'Pesanan minimum produk harus berupa angka',
            'product_minimum_order.min'               => 'Pesanan minimum produk minimal 1',
            'product_minimum_order.max'               => 'Pesanan minimum produk maksimal 99.999',

            'product_insurance.required'              => 'Asuransi produk harus diisi',
            'product_insurance.integer'               => 'Asuransi produk harus berupa angka',
            'product_insurance.between'               => 'Asuransi produk harus antara 1 dan 2',

            'product_preorder.integer'                => 'Preorder harus berupa angka',
            'product_preorder.between'                => 'Preorder harus antara 1 dan 2',

            'product_size_long.required'              => 'Panjang produk harus diisi',
            'product_size_long.integer'               => 'Panjang produk harus berupa angka',
            'product_size_long.min'                   => 'Panjang produk minimal 0',
            'product_size_long.max'                   => 'Panjang produk maksimal 1.000.000',

            'product_size_wide.required'              => 'Lebar produk harus diisi',
            'product_size_wide.integer'               => 'Lebar produk harus berupa angka',
            'product_size_wide.min'                   => 'Lebar produk minimal 0',
            'product_size_wide.max'                   => 'Lebar produk maksimal 1.000.000',

            'product_size_height.required'            => 'Tinggi produk harus diisi',
            'product_size_height.integer'             => 'Tinggi produk harus berupa angka',
            'product_size_height.min'                 => 'Tinggi produk minimal 0',
            'product_size_height.max'                 => 'Tinggi produk maksimal 1.000.000'
        ];
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'product_name'              => 'required|string|min:6|max:100',
            'product_description'       => 'required|string|min:10|max:2000',
            'product_images'            => 'required|array|min:1',
            'product_images.*'          => 'required|mimes:jpeg,jpg,png|max:10000',
            'product_price'             => 'required|integer|min:10|max:1000000000', // max 1 Milyar
            'product_discount'          => 'integer|min:10|max:1000000000',
            'product_stock'             => 'required|integer|min:1|max:999999',
            'product_weight'            => 'required|integer|min:0|max:999999',
            'product_condition'         => 'required|integer|between:1,2',
            'product_minimum_order'     => 'required|integer|min:1|max:99999',
            'product_insurance'         => 'required|integer|between:1,2',
            'product_preorder'          => 'integer|between:1,2',
            'product_size_long'         => 'required|integer|min:0|max:1000000', // max 1 Juta cm
            'product_size_wide'         => 'required|integer|min:0|max:1000000', // max 1 Juta cm
            'product_size_height'       => 'required|integer|min:0|max:1000000'  // max 1 Juta cm
        ];
    }
}
