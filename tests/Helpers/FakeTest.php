<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Faker\Generator;
use Lion\Bundle\Helpers\Fake;
use Lion\Test\Test;

class FakeTest extends Test
{
    private Fake $fake;

    protected function setUp(): void
    {
        $this->fake = new Fake();
    }

    public function testGet(): void
    {
        $this->assertInstanceOf(Generator::class, $this->fake->get());
    }
}
