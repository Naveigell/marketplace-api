<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'api/v1'], function(){

    // feed
    Route::get('/home/feed', 'Api\Buyer\Home\RecommendationController@getRecommendation');

    // product
    Route::get('/product/{slug}', 'Api\Buyer\Product\ProductDetailController@getProductDetail');

    // user
    Route::post('/auth/login', 'Api\Users\UserController@login');

    // profile -- image
    Route::post('/profile/image', 'Api\Users\Profile\ProfileController@updateImageProfile');
    // profile -- address
    Route::get('/profile/address', 'Api\Users\Profile\AddressController@getAddress');
    Route::get('/profile/address/{page}', 'Api\Users\Profile\AddressController@getAddressAtPage')->name('address-pagination');
    Route::put('/profile/address', 'Api\Users\Profile\AddressController@insertAddress');
    Route::post('/profile/address', 'Api\Users\Profile\AddressController@updateAddress');
    Route::post('/profile/address/toggle/active', 'Api\Users\Profile\AddressController@toggle');
    Route::delete('/profile/address', 'Api\Users\Profile\AddressController@deleteAddress');
    // profile -- username
    Route::post('/profile/name', 'Api\Users\Profile\ProfileController@updateName');
    Route::post('/profile/gender', 'Api\Users\Profile\ProfileController@updateGender');
    Route::post('/profile/birthday', 'Api\Users\Profile\ProfileController@updateBirthday');
    // province, city
    Route::get('/province', 'Api\Buyer\Location\LocationController@getProvince');
    Route::get('/province/{id}/city', 'Api\Buyer\Location\LocationController@getCityByProvinceId');

    // my shop -- all product
    Route::get('/dashboard/shop/product', 'Api\Seller\Shop\Product\ProductController@getProduct');
    Route::put('/dashboard/shop/product', 'Api\Seller\Shop\Product\ProductController@insertProduct');
    Route::get('/dashboard/shop/product/{page}', 'Api\Seller\Shop\Product\ProductController@getProductAtPage')->name('shop-product-pagination');
    // my shop -- archive product
    Route::post('/dashboard/shop/product/archive', 'Api\Seller\Shop\Product\ProductController@archiveProduct');
    Route::post('/dashboard/shop/product/unarchive', 'Api\Seller\Shop\Product\ProductController@unarchiveProduct');

    // cart
    Route::get('/cart', 'Api\Buyer\Cart\CartController@get');
    Route::post('/cart/toggle/active', 'Api\Buyer\Cart\CartController@toggle');
    Route::put('/cart', 'Api\Buyer\Cart\CartController@update');
    Route::delete('/cart', 'Api\Buyer\Cart\CartController@delete');
});
