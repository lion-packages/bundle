<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * The ProcessCommand class provides a simple and efficient way to execute
 * system commands in real time using the symfony/process component. It is
 * designed to facilitate the execution of shell commands, allowing the option
 * to display the output in real time and handle errors effectively
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class ProcessCommand
{
    /**
     * Run a process in real time with Process from the symfony/process
     * component
     *
     * @param string $commandString [Command that is executed]
     * @param bool $showOutput [Enable if the command is executed in real time]
     *
     * @return void
     *
     * @throws ProcessFailedException [If there is an error in the execution of
     * the command]
     */
    public static function run(string $commandString, bool $showOutput = true): void
    {
        $process = Process::fromShellCommandline($commandString);

        $process->setTimeout(null);

        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        if (!$showOutput) {
            $process->disableOutput();

            $process->run();
        } else {
            $process->run(function ($type, $buffer): void {
                echo $buffer;
            });
        }

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
