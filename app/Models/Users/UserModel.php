<?php

namespace App\Models\Users;

use Illuminate\Database\Eloquent\Model;
use App\Models\Users\UserModel;

class UserModel extends Model {
    protected $table = 'akun';

    /**
     * Ambil user berdasarkan email
     * @param  string $email
     * @return array
     */
    public function getUserByEmail($email) {
        return UserModel::select(['id_akun', 'email', 'password', 'toko.id_toko'])->join('toko', 'toko.toko_id_akun', '=', 'id_akun')->where(['email' => $email])->get();
    }

    /**
     * Mengambil email lewat id
     * @param  int $id_user
     * @return object
     */
    public function getCustomerDetail($id_user) {
        return UserModel::select(['akun.email', 'akun.akun_username', 'biodata.nomor_hp'])->join('biodata', 'biodata.biodata_id_akun', '=', 'akun.id_akun')->where('id_akun', $id_user)->first();
    }

}
