<?php

declare(strict_types=1);

namespace App\Traits\Framework;

use Symfony\Component\Console\Output\OutputInterface;

trait ConsoleOutput
{
    public function echo(OutputInterface $output, string $message): void
    {
        $output->writeln($message);

        logger($message, "error", [
            'type' => "Console",
            'proccess' => $this->getDefaultName()
        ], false);
    }

    public function errorOutput(string $message = ""): string
    {
        return "\033[0;31m{$message}\033[0m";
    }

    public function successOutput(string $message = ""): string
    {
        return "\033[0;32m{$message}\033[0m";
    }

    public function warningOutput(string $message = ""): string
    {
        return "\033[0;33m{$message}\033[0m";
    }

    public function infoOutput(string $message = ""): string
    {
        return "\033[0;36m{$message}\033[0m";
    }

    public function purpleOutput(string $message = ""): string
    {
        return "\033[0;95m{$message}\033[0m";
    }

}
