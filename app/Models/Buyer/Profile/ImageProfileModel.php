<?php

namespace App\Models\Buyer\Profile;

use Illuminate\Database\Eloquent\Model;

use App\Models\Buyer\Profile\ImageProfileModel;

class ImageProfileModel extends Model {
    protected $table = 'akun';

    /**
     * Ambil image terakhir dari akun
     * @param  int $id_user
     * @return array
     */
    public function getLastImage($id_user) {
        return ImageProfileModel::select('akun_foto_url')->where('id_akun', $id_user)->get();
    }

    /**
     * Fungsi untuk update profile image user
     * @param  int $id_user
     * @param  string $image_name 
     * @return int             lebih dari 0 berarti true, sebaliknya false
     */
    public function updateImage($id_user, $image_name) {
        return ImageProfileModel::where('id_akun', $id_user)->update([
            'akun_foto_url' => $image_name
        ]);
    }
}
