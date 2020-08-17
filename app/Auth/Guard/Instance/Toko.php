<?php
namespace App\Auth\Guard\Instance;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

use App\Helper\JWT;

class Toko {
    /**
     * Menyimpan nilai dari object toko
     * @var object
     */
    private $toko;

    public function __construct($toko) {
        $this->toko = $toko;
    }

    /**
     * Mengembalikan id dari toko
     * @return int
     */
    public function id() {
        return optional($this->toko)->id;
    }
}


 ?>
