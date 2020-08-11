<?php

namespace App\Http\Controllers\Api\Users\Profile;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

use Intervention\Image\ImageManagerStatic as Image;

use App\Models\Buyer\Profile\ImageProfileModel;
use App\Models\Buyer\Profile\ProfileModel;

use App\Http\Requests\Buyer\Profile\NameRequestUpdate;
use App\Http\Requests\Buyer\Profile\GenderRequestUpdate;
use App\Http\Requests\Buyer\Profile\BirthdayRequestUpdate;
use App\Http\Requests\Buyer\Profile\ImageProfileRequestUpdate;

use Config;
use Carbon\Carbon;

class ProfileController extends Controller {

    private $monthMaximumDate = [
        "1"   => 31,      // januari
        "2"   => 28,      // februari
        "3"   => 31,      // maret
        "4"   => 30,      // april
        "5"   => 31,      // mei
        "6"   => 30,      // juni
        "7"   => 31,      // juli
        "8"   => 31,      // agustus
        "9"   => 30,      // september
        "10"  => 31,      // oktober
        "11"  => 30,      // november
        "12"  => 31       // desember
    ];

    private $month = [
        "1" => "Januari",
        "2" => "Februari",
        "3" => "Maret",
        "4" => "April",
        "5" => "Mei",
        "6" => "Juni",
        "7" => "Juli",
        "8" => "Agustus",
        "9" => "September",
        "10" => "Oktober",
        "11" => "November",
        "12" => "Desember"
    ];

    /**
     * Attribute untuk menyimpan token yang sudah di decode
     * @var App\Auth\Guard\TokenGuard | null
     */
    private $user;

    /**
     * Attribute untuk menyimpan model ImageProfileModel
     * @var App\Models\Buyer\Profile\ImageProfileModel
     */
    private $imageModel;

    /**
     * Attribute untuk menyimpan model ProfileModel
     * @var App\Models\Buyer\Profile\ProfileModel
     */
    private $profileModel;

    /**
     * Create a class and make user attribute into decoded token
     */
    public function __construct() {
        $this->user = auth('user')->decodeToken('name');
        $this->imageModel = new ImageProfileModel;
        $this->profileModel = new ProfileModel;
    }

    /**
     * FUngsi untuk update gender user
     * @param  GenderRequestUpdate $request
     * @return json
     */
    public function updateGender(GenderRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;
            $row = $this->profileModel->updateGender($user->id(), $request->gender);

            if ($row > 0) {
                return json([
                    "message"   => "Update jenis kelamin berhasil",
                    "date"      => date("d-m-Y H:i")
                ]);
            }

            return error500(null, null, "Terjadi masalah saat mengganti jenis kelamin");
        }

        return error401();
    }

    public function updateBirthday(BirthdayRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;

            $day      = $request->day;
            $month    = $request->month;
            $year     = $request->year;

            $isLeapYear = ($year % 4 == 0 && $year % 100 != 0) || ($year % 100 == 0 && $year % 400 == 0);

            if ($this->isYearDateValid($isLeapYear, $day, $month)) {
                $row = $this->profileModel->updateBirthday($user->id(), date("Y-m-d", strtotime($year."-".$month."-".$day)));

                if ($row > 0) {
                    return json([
                        "message"     => "Ubah tanggal lahir berhasil",
                        "date"        => date("d-m-Y H:i")
                    ]);
                }

                return error500(null, null, "Terjadi masalah saat mengubah tanggal lahir");
            }

            return error422(null, "date", "Tanggal yang dimasukkan tidak valid, silakan ubah kembali tanggal lahir");
        }

        return error401();
    }

    /**
     * Fungsi untuk mengecek apakah date yang dimasukkan valid
     * untuk tahun kabisat ataupun tidak
     * @param  boolean $isLeapYear
     * @param  int  $date
     * @param  int  $month
     * @return boolean
     */
    private function isYearDateValid($isLeapYear, $day, $month) {
        if ($isLeapYear) {
            // jika bulan = 2 dan tahun kabisat, maka ambil default bulan ke 2 lalu di tambah 1
            // jika bulan != 2 dan tahun kabisat, maka hanya ambil default bulan dan jangan ditambah 1
            return ($day > 0 && $day <= $this->monthMaximumDate[$month] && $month != 2) || ($day > 0 && $day <= $this->monthMaximumDate[2] + 1 && $month == 2);
        }
        return $day > 0 && $day <= $this->monthMaximumDate[$month];
    }

    /**
     * Update profile image
     * @param  ImageProfileRequest $request
     * @return json
     */
    public function updateImageProfile(ImageProfileRequestUpdate $request) {

        if ($this->user->exists()) {
            $user     = $this->user;
            $image    = $request->file('image');
            $detail   = $this->processImageDetail($image);
            $size     = $image->getSize();

            // buat random string untuk image
            // huruf depan adalah id user
            $name     = $user->id().$this->randomImageString(45, $image->extension());

            // ambil image terakhir
            $latestImage = $this->imageModel->getLastImage($user->id());
            $latestImage = $latestImage[0]->akun_foto_url;

            $newImageDestination    = public_path('/assets/images/users/');
            $latestImageDestination = public_path("/assets/images/users/$latestImage");

            if ($image->move($newImageDestination, $name)) {

                if ($this->imageModel->updateImage($user->id(), $name)) {
                    // hapus image terakhir jika ada, untuk mengosongkan memori
                    // jika hapus gagal tidak masalah
                    if (file_exists($latestImageDestination)) {
                        unlink($latestImageDestination);
                    }

                    return json([
                       "message" => "Upload gambar berhasil",
                       "image"   => [
                          "link" => config('app.assets_url_images_users')."/".$name,
                          "image_size" => [
                              "width"   => $detail->width(),
                              "height"  => $detail->height()
                          ],
                          "file_size" => number_format($size/(1024 * 1024), 1)." MB",
                          "name" => $name,
                          "date" => date("d-m-Y H:i")
                       ]
                    ]);
                }
            }
            return error500(null, "image", "Gagal saat mengupload gambar");
        }
        return error401();
    }

    /**
     * Update username dari profile
     * @param  NameRequestUpdate $request
     * @return json
     */
    public function updateName(NameRequestUpdate $request) {
        if ($this->user->exists()) {
            $user = $this->user;
            $row  = $this->profileModel->updateName($user->id(), $request->name);

            if ($row > 0) {
                return json([
                    "message"   => "Nama berhasil diubah",
                    "date"      => date("d-m-Y H:i")
                ]);
            }

            return error500(null, null, "Terjadi kesalahan saat mengganti nama");
        }

        return error401();
    }

    /**
     * Generate random string untuk image
     * @param  integer $count
     * @param  Image   $image
     * @return string
     */
    public function randomImageString($count = 30, $extension) {
        return Str::random($count).date("_d_m_Y_H_i_s.").$extension;
    }

    /**
     * Fungsi untuk memproses dan mengambil lebar dan tinggi image
     * melalui class
     * @param  Image $image
     * @return Class
     */
    private function processImageDetail($image){

        $detail = getimagesize($image);

        // ambil image width & height melalui sebuah class
        $img = new class($detail) {
            private $detail;

            public function __construct($detail){
                $this->detail = $detail;
            }

            public function width(){
                return $this->detail[0];
            }

            public function height(){
                return $this->detail[1];
            }
        };

        return new $img($detail);
    }
}
