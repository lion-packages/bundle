<?php

declare(strict_types=1);

namespace Tests\Support;

use Closure;
use Lion\Bundle\Support\ExceptionHandler;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class ExceptionHandlerTest extends Test
{
    private ExceptionHandler $exceptionHandler;

    /**
     * @var array{
     *     addInformation: bool,
     *     callback: Closure|null,
     * } $options
     */
    private array $options;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->exceptionHandler = new ExceptionHandler();

        $this->options = [
            'addInformation' => true,
            'callback' => function (): int {
                return 1;
            },
        ];

        $this->initReflection($this->exceptionHandler);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function handle(): void
    {
        $this->exceptionHandler->handle($this->options);

        $options = $this->getPrivateProperty('options');

        $this->assertIsArray($options);
        $this->assertNotEmpty($options);
        $this->assertSame($this->options, $options);
    }

    #[Testing]
    public function getOptions(): void
    {
        $this->exceptionHandler->handle($this->options);

        $options = $this->exceptionHandler->getOptions();

        $this->assertSame($this->options, $options);
    }
}
