<?php

namespace App\Console\Framework\Token;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{ InputInterface, InputArgument };
use Symfony\Component\Console\Output\OutputInterface;
use LionSecurity\{ RSA, JWT };

class GenerateJWTCommand extends Command {

    protected static $defaultName = "token:jwt";

    protected function initialize(InputInterface $input, OutputInterface $output) {
        $output->writeln("<comment>Generating JWT...</comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this->setDescription(
            "Created command to generate JWT token"
        )->addArgument(
            'session', InputArgument::REQUIRED, '', null
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $output->writeln(
            JWT::encode([
                'session' => $input->getArgument('session') === 'true' ? true : false,
                'system' => "Lion Framework",
                'autor' => "Sergio Leon",
                'github' => "https://github.com/Sleon4/Lion-Framework"
            ])
        );

        $output->writeln("<info>JWT created successfully</info>");
        return Command::SUCCESS;
    }

}