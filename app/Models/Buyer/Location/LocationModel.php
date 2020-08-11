<?php

namespace App\Models\Buyer\Location;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

use App\Models\Buyer\Location\LocationModel;

class LocationModel extends Model {
    protected $table = 'provinsi';

    public function getProvince() {
        return LocationModel::all();
    }

    public function getCityByProvinceId($id) {
        return DB::table('kota')->where('kota_id_provinsi', $id)->get();
    }
}
