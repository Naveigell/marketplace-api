<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\Users\UserModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use App\Helper\JWT;

class UserController extends Controller {

    /**
     * Attribute untuk menyimpan token yang sudah di decode
     * @var App\Auth\Guard\TokenGuard | null
     */
    private $user;

    /**
     * Attribute untuk menyimpan model dari user
     * @var App\Models\Users\UserModel
     */
    private $userModel;

    public function __construct(){
        $this->user = auth('user')->decodeToken('name');
        $this->userModel = new UserModel;
    }

    /**
     * Logout untuk user
     * @return json
     */
    public function logout() {
        if ($this->user->exists()) {

            // user diasumsikan login melalui web
            // jika cookie tidak null
            $isUserLoginOnWeb = Cookie::get('name') != null;

            Cookie::queue(Cookie::forget('name'));

            if ($isUserLoginOnWeb) {
                return redirect()->back()->with('message', 'Berhasil logout');
            }

            return json([
                'message'   => 'User berhasil logout',
                'date'      => now()
            ]);
        }
        return error401();
    }

    /**
     * Fungsi login
     * @param  Request $request
     * @return json
     */
    public function login(Request $request){
        $user = $this->userModel->getUserByEmail($request->email);

        if (count($user) <= 0) {
            return error(null, ["user" => ["Pengguna tidak ditemukan"]], 404);
        }

        $user = $user[0];
        if ($user->password == $request->password) {
            $data = [
                "user" => [
                    "login_date" => [
                       "time" => date("H:i"),
                       "date" => date("m-d-Y")
                    ],
                    "id"        => $user->id_akun,
                    "toko"      => [
                        "id"    => $user->id_toko
                    ]
                ]
            ];

            $jwt = JWT::encode($data);

            Cookie::queue('name', $jwt, 1 * 60 * 24 * 30 * 365 * 10);

            return json(["auth" => [
               "message" => "Login berhasil",
               "token" => $jwt,
               "login_date" => [
                  "time" => date("H:i"),
                  "date" => date("d-m-Y")
               ],
               "expired" => "+10 Tahun"
            ]]);
        }

        return error422(null, "password", "Password salah");
    }
}
