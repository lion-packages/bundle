<?php

declare(strict_types=1);

namespace Tests\Commands\Lion\Queue;

use DI\DependencyException;
use DI\NotFoundException;
use Lion\Bundle\Commands\Lion\Queue\RunQueuedTasksCommand;
use Lion\Bundle\Helpers\Commands\Queue\TaskQueue;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use ReflectionException;
use Symfony\Component\Console\Application;
use PHPUnit\Framework\Attributes\Test as Testing;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

class RunQueuedTasksCommandTest extends Test
{
    private RunQueuedTasksCommand $runQueuedTasksCommand;

    /**
     * @throws ReflectionException
     * @throws DependencyException
     * @throws NotFoundException
     */
    protected function setUp(): void
    {
        /** @var RunQueuedTasksCommand $runQueuedTasksCommand */
        $runQueuedTasksCommand = new Container()->resolve(RunQueuedTasksCommand::class);

        $this->runQueuedTasksCommand = $runQueuedTasksCommand;

        $application = new Application();

        $application->add($this->runQueuedTasksCommand);

        $this->initReflection($this->runQueuedTasksCommand);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function setContainer(): void
    {
        $this->assertInstanceOf(
            RunQueuedTasksCommand::class,
            $this->runQueuedTasksCommand->setContainer(new Container())
        );

        $this->assertInstanceOf(Container::class, $this->getPrivateProperty('container'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function initialize(): void
    {
        $input = new ArrayInput([]);

        $output = new BufferedOutput();

        $this->getPrivateMethod('initialize', [
            'input' => $input,
            'output' => $output
        ]);

        $taskQueue = $this->getPrivateProperty('taskQueue');

        $this->assertInstanceOf(TaskQueue::class, $taskQueue);
    }
}
