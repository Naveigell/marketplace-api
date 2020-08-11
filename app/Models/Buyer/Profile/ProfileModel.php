<?php

namespace App\Models\Buyer\Profile;

use Illuminate\Database\Eloquent\Model;

use App\Models\Buyer\Profile\ProfileModel;

class ProfileModel extends Model {
    protected $table = 'biodata';

    public $timestamps = false;

    /**
     * Update username user
     * @param  int $id_user
     * @param  string $username
     * @return int mengembalikan banyaknya row yang terupdate
     */
    public function updateName($id_user, $name) {
        return ProfileModel::where([
            'biodata_id_akun'     => $id_user
        ])->update(['nama' => $name]);
    }

    /**
     * Update gender user
     * @param  int $id_user 
     * @param  int $gender
     * @return int mengembalikan banyaknya row yang terupdate
     */
    public function updateGender($id_user, $gender) {
        return ProfileModel::where([
            'biodata_id_akun'     => $id_user
        ])->update(['jenis_kelamin' => $gender == 1 ? 'Laki - laki' : 'Perempuan']);
    }

    /**
     * Update gender user
     * @param  int $id_user
     * @param  int $date
     * @return int mengembalikan banyaknya row yang terupdate
     */
    public function updateBirthday($id_user, $date) {
        return ProfileModel::where([
            'biodata_id_akun'     => $id_user
        ])->update(['tanggal_lahir' => $date]);
    }
}
