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

}
