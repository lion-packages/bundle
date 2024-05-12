<?php

declare(strict_types=1);

namespace Tests\Support\Exceptions;

use Lion\Bundle\Support\Exceptions\ExceptionSupport;
use Lion\Request\Response;
use Lion\Test\Test;

class ExceptionSupportTest extends Test
{
    private ExceptionSupport $exceptionSupport;

    protected function setUp(): void
    {
        $this->exceptionSupport = new ExceptionSupport();
    }

    public function testGetStatus()
    {
        $status = $this->exceptionSupport->getStatus();

        $this->assertSame(Response::ERROR, $status);
    }

    public function testSetStatus()
    {
        $this->exceptionSupport->setStatus(Response::SUCCESS);

        $this->assertSame(Response::SUCCESS, $this->exceptionSupport->getStatus());
    }

    public function testGetData()
    {
        $this->assertNull($this->exceptionSupport->getData());
    }

    public function testSetData()
    {
        $newData = ['key' => 'value'];

        $this->exceptionSupport->setData($newData);

        $this->assertSame($newData, $this->exceptionSupport->getData());
    }
}
