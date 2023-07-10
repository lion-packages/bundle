<?php

namespace App\Http;

use App\Traits\Framework\Http;
use App\Traits\Framework\Session;
use App\Traits\Framework\Singleton;

class Kernel {

    use Singleton, Http, Session;

}
