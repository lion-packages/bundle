<?php

namespace App\Console\Framework\AES;

use App\Traits\Framework\AES;
use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewAESCommand extends Command
{
    use ConsoleOutput, AES;

	protected static $defaultName = "aes:new";

	protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Command to create KEY and IV keys for AES");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln($this->warningOutput("\t>>  AES KEY: {$this->generateKeys()}"));
        $output->writeln($this->warningOutput("\t>>  AES IV: {$this->generateKeys()}"));
        $output->writeln($this->successOutput("\t>>  Keys created successfully"));
        return Command::SUCCESS;
    }

}
