<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\ProcessCommand;
use Lion\Test\Test;
use Symfony\Component\Process\Exception\ProcessFailedException;

class ProcessCommandTest extends Test
{
    private ProcessCommand $processCommand;

    protected function setUp(): void
    {
        $this->processCommand = new ProcessCommand();
    }

    public function testRun(): void
    {
        $this->processCommand->run('echo "Hello, World!" > /tmp/test_output.txt');

        $this->assertEquals("Hello, World!\n", file_get_contents('/tmp/test_output.txt'));
    }

    public function testRunNotOutput(): void
    {
        $this->processCommand->run('echo "Hello, World!"', false);

        $this->expectOutputString('');
    }

    public function testRunWithFailure(): void
    {
        $errorFile = tempnam(sys_get_temp_dir(), 'test_error');

        try {
            $this->processCommand->run('non_existent_command 2>' . $errorFile);
        } catch (ProcessFailedException $exception) {
            $errorOutput = file_get_contents($errorFile);

            $this->assertStringContainsString('not found', $errorOutput);

            return;
        }

        $this->fail('Expected ProcessFailedException was not thrown');
    }
}
