<?php

declare(strict_types=1);

namespace Tests\Commands\Lion;

use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;

class ServerCommandTest extends Test
{
    #[Testing]
    public function execute(): void
    {
        $this->expectNotToPerformAssertions();
    }
}
