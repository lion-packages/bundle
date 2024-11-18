<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Exception;
use Lion\Bundle\Commands\Lion\New\TestCommand;
use Lion\Command\Command;
use Lion\Command\Kernel;
use Lion\Dependency\Injection\Container;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Tester\CommandTester;

class TestCommandTest extends Test
{
    private const string URL_PATH = './tests/';
    private const string CLASS_NAME = 'TestTest';
    private const string FILE_NAME = self::CLASS_NAME . '.php';
    private const string OUTPUT_MESSAGE = 'test has been generated';

    private CommandTester $commandTester;

    protected function setUp(): void
    {
        $application = (new Kernel())->getApplication();

        $application->add((new Container())->resolve(TestCommand::class));

        $this->commandTester = new CommandTester($application->find('new:test'));
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        (new Store())->remove('./tests/' . self::FILE_NAME);
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['test' => self::CLASS_NAME]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists(self::URL_PATH . self::FILE_NAME);
    }
}
