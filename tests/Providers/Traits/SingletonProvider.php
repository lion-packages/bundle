<?php

declare(strict_types=1);

namespace Tests\Providers\Traits;

use Lion\Bundle\Interface\SingletonInterface;
use Lion\Bundle\Traits\SingletonTrait;

class SingletonProvider implements SingletonInterface
{
    use SingletonTrait;
}
