<?php

namespace App\Http;

use App\Traits\Framework\HttpTrait;
use App\Traits\Framework\Session;
use App\Traits\Framework\Singleton;

class Kernel {

    use Singleton, HttpTrait, Session;

}
