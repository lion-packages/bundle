<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class ExceptionCoreTest extends Test
{
    #[Testing]
    public function exceptionHandler(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
