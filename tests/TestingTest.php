<?php

declare(strict_types=1);

namespace Tests;

use Lion\Test\Test;

class TestingTest extends Test
{
    public function test(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
