<?php

namespace App\Http\Controllers\Api\Users\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\Buyer\Address\AddressRequestInsert;
use Illuminate\Http\Request;

use App\Models\Buyer\Profile\AddressModel;

class AddressController extends Controller {

    /**
     * Attribute untuk menyimpan user
     * @var App\Auth\Guard\TokenGuard
     */
    private $user;

    /**
     * Attribute address model
     * @var App\Models\Buyer\Profile\AddressModel
     */
    private $addressModel;

    public function __construct() {
        $this->user = auth('user')->decodeToken('name');
        $this->addressModel = new AddressModel;
    }

    /**
     * Fungsi untuk membuat alamat menjadi lebih rapi dengan memisahkan
     * lokasi pada alamata menjadi sebuah object
     * @param  object $address
     * @return array
     */
    private function generateAddressFormat(object $address) {
        $data      = [];

        foreach ($address as $addr) {
            $add     = new \stdClass;
            $lokasi  = new \stdClass;

            // masukkan berdasarkan lokasi
            $lokasi->nama_provinsi  = $addr->nama_provinsi;
            $lokasi->tipe           = $addr->tipe;
            $lokasi->nama_kota      = $addr->nama_kota;
            $lokasi->postal_code    = $addr->postal_code;

            $add->id_alamat        = $addr->id_alamat;
            $add->nama_penerima    = $addr->nama_penerima;
            $add->nama_alamat      = $addr->nama_alamat;
            $add->no_hp_penerima   = $addr->no_hp_penerima;

            $add->alamat_lengkap   = $addr->alamat_lengkap;
            $add->alamat_utama     = $addr->alamat_utama;
            $add->lokasi           = $lokasi;

            // if ($add->no_hp_penerima[0] == "0") {
            //     $add->no_hp_penerima = "+62".substr($add->no_hp_penerima, 1, strlen($add->no_hp_penerima));
            // }

            array_push($data, $add);
        }

        return $data;
    }

    /**
     * Fungsi untuk mengambil alamat dari user berdasarkan id
     * @param  Request $request
     * @return json
     */
    public function getAddress() {
        if ($this->user->exists()) {
            $user = $this->user;

            $address   = $this->addressModel->getAddress($user->id());
            $data      = $this->generateAddressFormat($address);

            $length = count($data);

            return json([
                "message" => $length > 0 ? "Ambil alamat berhasil" : "Tidak ada alamat yang ditemukan",
                "address" => $data,
                "total"   => $length
            ]);
        }

        // un authorized
        return error401();
    }

    public function getAddressAtPage($page) {
        if ($this->user->exists()) {
            $user = $this->user;
            $take = 10;

            $address = $this->addressModel->getAddressAtPage($user->id(), $page, $take);

            // ceil untuk menghindari desimal
            $availablePage     = ceil($address->total/$take);
            $next              = $page < $availablePage ? $page + 1 : null;
            $previous          = $page > 1 && !($page > $availablePage) ? $page - 1 : null;

            $currentPageUrl    = route('address-pagination', ['page' => $page]);
            $lastPageUrl       = route('address-pagination', ['page' => $availablePage]);
            $firstPageUrl      = route('address-pagination', ['page' => 1]);

            $nextPageUrl       = $next == null     ? null : route('address-pagination', ['page' => $next]);
            $previousPageUrl   = $previous == null ? null : route('address-pagination', ['page' => $previous]);

            $response = [
                "message"           => count($address->data) == 0 ? "Alamat tidak ditemukan" : "Alamat berhasil ditemukan",
                "available_page"    => $availablePage,
                "page"              => [
                    "current"       => $currentPageUrl,
                    "next"          => $nextPageUrl,
                    "previous"      => $previousPageUrl,
                    "first"         => $firstPageUrl,
                    "last"          => $lastPageUrl
                ],
                "total_address"     => $address->total,
                "address"           => $address->data,
            ];

            return json($response);
        }

        return error401();
    }

    /**
     * Insert alamat baru
     * @param  App\Http\Requests\Buyer\Address\AddressRequestInsert $request
     * @return json
     */
    public function insertAddress(AddressRequestInsert $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            $inserted = $this->addressModel->insertAddress(
                $user->id(),
                $request->id_kota,
                $request->nama_alamat,
                $request->nama_penerima,
                $request->no_hp_penerima,
                $request->alamat_lengkap
            );

            if ($inserted) {
                return json([
                    "message"     => "Tambah alamat berhasil",
                    "date"        => date("d-m-Y H:i")
                ]);
            }

            return error500(null, "address", "Terjadi masalah saat menambah alamat");
        }

        return error401();
    }

    /**
     * Toggle untuk membuat alamat utama
     * @param  Request $request
     * @return json
     */
    public function toggle(Request $request) {

        if ($this->user->exists()) {
            $user = $this->user;

            $toggleSuccess = $this->addressModel->toggleActiveAddress($user->id(), $request->id_alamat);

            if ($toggleSuccess) {
                return json([
                    'message' => "Alamat utama berhasil diubah",
                    'date' => date('d-m-Y H:i')
                ]);
            }

            return error500(null, "toggle", "Terjadi masalah saat mengubah alamat utama");
        }

        // un authorized
        return error401();
    }
}
