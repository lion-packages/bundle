<?php

namespace App\Console\Framework\Token;

use LionSecurity\JWT;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJWTCommand extends Command {

    protected static $defaultName = "token:jwt";

    protected function initialize(InputInterface $input, OutputInterface $output) {

    }

    protected function interact(InputInterface $input, OutputInterface $output) {

    }

    protected function configure() {
        $this
            ->setDescription("Created command to generate JWT token")
            ->addArgument('session', InputArgument::OPTIONAL, 'Session must be true or false', "true");
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $jwt = JWT::encode([
            'session' => $input->getArgument('session') === "true" ? true : false,
            'system' => "Lion-Framework",
            'autor' => "Sergio Leon",
            'github' => "https://github.com/Sleon4"
        ]);

        $output->writeln("<comment>\t>>  TOKEN: JWT created successfully</comment>");
        $output->writeln("<info>\t>>  TOKEN: {$jwt}</info>");

        return Command::SUCCESS;
    }

}
