<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\New;

use Lion\Bundle\Commands\Lion\New\RSACommand;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Security\RSA;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class RSACommandTest extends Test
{
    private const string URL_PATH = 'keys/';
    private const string OUTPUT_MESSAGE = 'public and private';

    private CommandTester $commandTester;
    private RSACommand $rSACommand;

    protected function setUp(): void
    {
        $application = new Application();

        $this->rSACommand = (new RSACommand())
            ->setRSA(new RSA())
            ->setStore(new Store());

        $application->add($this->rSACommand);

        $this->commandTester = new CommandTester($application->find('new:rsa'));
    }

    protected function tearDown(): void
    {
        $this->rmdirRecursively('./storage/keys/');
    }

    #[Testing]
    public function execute(): void
    {
        $this->assertSame(Command::SUCCESS, $this->commandTester->execute(['--path' => self::URL_PATH]));
        $this->assertStringContainsString(self::OUTPUT_MESSAGE, $this->commandTester->getDisplay());
        $this->assertFileExists('./storage/' . self::URL_PATH . 'public.key');
        $this->assertFileExists('./storage/' . self::URL_PATH . 'private.key');
    }
}
