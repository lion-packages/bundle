<?php

namespace App\Console\Framework\Key;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AESCommand extends Command {

	protected static $defaultName = "key:aes";

	protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Initializing AES service...</comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription("Command to create KEY and IV keys for AES");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $generateKeys = function() {
            $caracteresPermitidos = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@-_/';
            $bytes = random_bytes(16);
            $longitud = strlen($caracteresPermitidos);
            $key = '';

            for ($i = 0; $i < 16; $i++) {
                $indice = ord($bytes[$i]) % $longitud;
                $key .= $caracteresPermitidos[$indice];
            }

            return $key;
        };

        $output->writeln("\n\t<question> INFO </question> AES KEY: {$generateKeys()}\n");
        $output->writeln("\t<question> INFO </question> AES IV: {$generateKeys()}\n");
        $output->writeln("<info>Keys created successfully</info>");

        return Command::SUCCESS;
    }

}
