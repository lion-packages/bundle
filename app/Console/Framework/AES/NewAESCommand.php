<?php

namespace App\Console\Framework\AES;

use App\Traits\Framework\ConsoleOutput;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewAESCommand extends Command {

    use ConsoleOutput;

	protected static $defaultName = "aes:new";

	protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Command to create KEY and IV keys for AES");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $generateKeys = function() {
            $items = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@-_/';
            $bytes = random_bytes(16);
            $longitud = strlen($items);
            $key = '';

            for ($i = 0; $i < 16; $i++) {
                $indice = ord($bytes[$i]) % $longitud;
                $key .= $items[$indice];
            }

            return $key;
        };

        $output->writeln($this->warningOutput("\t>>  AES KEY: {$generateKeys()}"));
        $output->writeln($this->warningOutput("\t>>  AES IV: {$generateKeys()}"));
        $output->writeln($this->successOutput("\t>>  Keys created successfully"));
        return Command::SUCCESS;
    }

}
