<?php

namespace App\Models\Buyer\Profile;

use Illuminate\Database\Eloquent\Model;

use App\Models\Buyer\Profile\AddressModel;

class AddressModel extends Model {
    protected $table = 'alamat';

    /**
     * Set timestamps ke false karena tidak ada timestamps di table
     * @var boolean
     */
    public $timestamps = false;

    /**
     * Ambil alamat berdasarkan id user
     * @param  int $id_user
     * @return array
     */
    public function getAddress($id_user) {
        return AddressModel::select([
            'alamat.id_alamat', 'alamat.nama_penerima', 'alamat.nama_alamat', 'alamat.no_hp_penerima', 'alamat.alamat_lengkap', 'alamat.alamat_utama',
            'kota.nama_provinsi', 'kota.tipe', 'kota.nama_kota', 'kota.postal_code'
        ])->join('kota', 'alamat.alamat_id_kota', '=', 'kota.id_kota')->where('alamat_id_akun', $id_user)->get();
    }

    /**
     * Set alamat utama ke 0 lalu set ke 1 pada id alamat yang sesuai
     * @param  int $id_user
     * @param  int $id_alamat
     * @return boolean
     */
    public function toggleActiveAddress($id_user, $id_alamat) {
        // set alamat utama yang hanya bernilai 1 ke 0
        $rowsForInactiveAddress = AddressModel::where([
            'alamat_id_akun'  => $id_user,
            'alamat_utama'    => 1
        ])->update(['alamat_utama' => 0]);

        // kemudian set alamat utama ke 1 yang sesuai dengan id alamat
        $rowsForActiveAddress = AddressModel::where([
            'alamat_id_akun'  => $id_user,
            'id_alamat'    => $id_alamat
        ])->update(['alamat_utama' => 1]);

        return $rowsForActiveAddress > 0;
    }

    /**
     * Fungsi untuk mengambil alamat di page ke n
     * @param  int $id_user
     * @param  int $page
     * @param  int $take
     * @return object
     */
    public function getAddressAtPage($id_user, $page, $take) {
        $query = AddressModel::query();
        $query->where('alamat_id_akun', $id_user);

        // example
        // page = 2, take = 10
        // to = 2 * 10 = 20
        // from = 20 - 10
        $to       = $page * $take;
        $from     = $to - $take;

        $total    = $query->count();
        $address  = $query->take($take)->skip($from)->get();

        $data = new \stdClass;
        $data->total = $total;
        $data->data  = $address;

        return $data;
    }

    /**
     * Fungsi untuk menyimpan alamat baru ke dalam database
     * @param  int $id_user
     * @param  int $id_kota
     * @param  string $nama_alamat
     * @param  string $nama_penerima
     * @param  string $no_hp_penerima
     * @param  string $alamat_lengkap
     * @return int return banyaknya baris yang diinsert
     */
    public function insertAddress($id_user, $id_kota, $nama_alamat, $nama_penerima, $no_hp_penerima, $alamat_lengkap) {
        return AddressModel::insert([
            'alamat_id_akun'      => $id_user,
            'alamat_id_kota'      => $id_kota,
            'nama_alamat'         => $nama_alamat,
            'nama_penerima'       => $nama_penerima,
            'no_hp_penerima'      => $no_hp_penerima,
            'alamat_lengkap'      => $alamat_lengkap
        ]);
    }

    // not working
    public function setUpdatedAt($value) {
        return NULL;
    }

    public function setCreatedAt($value) {
        return NULL;
    }
}
