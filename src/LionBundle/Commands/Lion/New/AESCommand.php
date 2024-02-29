<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Bundle\Traits\AES;
use Lion\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AESCommand extends Command
{
    use AES;

    protected function configure(): void
    {
        $this
            ->setName('new:aes')
            ->setDescription('Command to create KEY and IV keys for AES');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->warningOutput("\t>>  AES KEY: {$this->generateKeys()}"));
        $output->writeln($this->warningOutput("\t>>  AES IV: {$this->generateKeys()}"));
        $output->writeln($this->successOutput("\t>>  Keys created successfully"));

        return Command::SUCCESS;
    }
}
