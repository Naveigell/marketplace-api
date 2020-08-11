<?php

namespace App\Http\Controllers\Api\Buyer\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Buyer\Location\LocationModel;

class LocationController extends Controller {

    private $user;
    private $locationModel;

    public function __construct(){
        $this->locationModel = new LocationModel;
    }

    public function getProvince() {
        $province = $this->locationModel->getProvince();

        return json([
            "message"         => "Ambil provinsi berhasil",
            "total"           => count($province),
            "province"        => $province
        ]);
    }

    public function getCityByProvinceId($id) {
        $city = $this->locationModel->getCityByProvinceId($id);

        return json([
            "message"         => "Ambil kota berhasil",
            "province"        => $city[0]->nama_provinsi,
            "total"           => count($city),
            "city"            => $city
        ]);
    }

}
