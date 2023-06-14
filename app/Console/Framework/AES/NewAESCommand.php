<?php

namespace App\Console\Framework\AES;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class NewAESCommand extends Command {

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

        $output->writeln("<comment>\t>>  AES KEY: {$generateKeys()}</comment>");
        $output->writeln("<comment>\t>>  AES IV: {$generateKeys()}</comment>");
        $output->writeln("<info>\t>>  Keys created successfully</info>");

        return Command::SUCCESS;
    }

}
