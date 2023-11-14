<?php

declare(strict_types=1);

namespace LionBundle\Commands\AES;

use LionBundle\Traits\AES;
use LionCommand\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewAESCommand extends Command
{
    use AES;

    protected function configure()
    {
        $this
            ->setName('aes:new')
            ->setDescription('Command to create KEY and IV keys for AES');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->warningOutput("\t>>  AES KEY: {$this->generateKeys()}"));
        $output->writeln($this->warningOutput("\t>>  AES IV: {$this->generateKeys()}"));
        $output->writeln($this->successOutput("\t>>  Keys created successfully"));

        return Command::SUCCESS;
    }
}
