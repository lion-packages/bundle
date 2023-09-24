<?php

declare(strict_types=1);

namespace App\Http;

use App\Traits\Framework\Http;
use App\Traits\Framework\Index;
use App\Traits\Framework\Session;
use App\Traits\Framework\Singleton;

class Kernel
{
    use Singleton, Index, Http, Session;
}
