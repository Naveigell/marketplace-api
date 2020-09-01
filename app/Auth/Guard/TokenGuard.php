<?php

namespace App\Auth\Guard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

use App\Helper\JWT;
use App\Auth\Guard\Instance\Toko;

class TokenGuard implements Guard {
    /**
     * Attribute untuk menyimpan request dari controller
     * @var Illuminate\Http\Request
     */
    protected $requests;

    /**
     * Attribute untuk menyimpan request token
     * @var object
     */
    protected $token;

    /**
     * Attribute untuk menyimpan class toko
     * @var [type]
     */
    private $toko;

    /**
     * Attribute untuk menimpan token yang sudah di decode
     * @var object NULL
     */
    private $userInstance = NULL;

    protected $request;
    protected $provider;
    protected $user;

    /**
     * Create a new authentication guard.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $provider
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    public function __construct(UserProvider $provider, Request $request) {
        $this->request = $request;
        $this->provider = $provider;
        $this->user = NULL;
    }

    /**
     * Fungsi untuk mengambil token berdasarkan nama kemudian di
     * simpan dalam attribute $token
     *
     * @param  string $string
     * @return void
     */
    private function token($string){
        $this->token = $this->request->bearerToken() == null ? $this->getCookie($string) : $this->request->bearerToken();
    }

    /**
     * Fungsi untuk mengambil token yang sudah di decode
     * @return App\Auth\User
     */
    public function decodeToken($name){
        $this->token = $this->request->bearerToken() == null ? $this->getCookie($name) : $this->request->bearerToken();
        $this->userInstance = $this->token == null ? null : [JWT::decode($this->token)];
        if ($this->userInstance != null) {
            $toko = $this->userInstance[0]->user->toko;
            $this->toko = new Toko($toko);
        }

        return $this;
    }

    /**
     * Fungsi untuk mengambil cookie berdasarkan nama
     * @param  string $name
     * @return string
     */
    public function getCookie($name){
        return Cookie::get($name);
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @return bool
     */
    public function check() {
       return ! is_null($this->user());
    }

    /**
     * Check jika token user sudah di otentikasi yang berarti user sudah login
     * @return boolean
     */
    public function exists() {
        return !is_null($this->userInstance);
    }

    /**
     * Determine if the current user is a guest.
     *
     * @return bool
     */
    public function guest() {
       return ! $this->check();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user() {
        if (!is_null($this->userInstance)) {
            return $this->userInstance[0]->user;
        }
    }

    /**
     * Get the ID for the currently authenticated user.
     *
     * @return string|null
    */
    public function id() {
        if (!is_null($this->userInstance)) {
            return $this->userInstance[0]->user->id;
        }
    }


    /**
     * Ambil ID Toko yang terautentikasi
     * @return mixed object|null
     */
    public function toko() {
        return $this->toko;
    }

    /**
     * Validate a user's credentials.
     *
     * @return bool
     */
    public function validate(Array $credentials=[]) {
        if (empty($credentials['username']) || empty($credentials['password'])) {
            if (!$credentials=$this->getJsonParams()) {
               return false;
            }
        }

        $user = $this->provider->retrieveByCredentials($credentials);

        if (! is_null($user) && $this->provider->validateCredentials($user, $credentials)) {
            $this->setUser($user);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Set the current user.
     *
     * @param  Array $user User info
     * @return void
     */
    public function setUser(Authenticatable $user) {
        $this->user = $user;
        return $this;
    }
}

 ?>
