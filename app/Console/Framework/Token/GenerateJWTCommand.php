<?php

namespace App\Console\Framework\Token;

use App\Traits\Framework\ConsoleOutput;
use LionSecurity\JWT;
use LionSecurity\RSA;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJWTCommand extends Command
{
    use ConsoleOutput;

    protected static $defaultName = "token:jwt";

    protected function initialize(InputInterface $input, OutputInterface $output)
    {

    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {

    }

    protected function configure()
    {
        $this
            ->setDescription("Created command to generate JWT token")
            ->addArgument('session', InputArgument::OPTIONAL, 'Session must be true or false', "true")
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Save to a specific path?');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption("path");
        if ($path != null) RSA::setPath($path);

        $jwt = JWT::encode([
            'session' => $input->getArgument('session') === "true" ? true : false,
            'system' => "Lion-Framework",
            'autor' => "Sergio Leon",
            'github' => "https://github.com/lion-packages"
        ]);

        $output->writeln($this->warningOutput("\t>>  TOKEN: JWT created successfully"));
        $output->writeln($this->successOutput("\t>>  TOKEN: {$jwt->data->jwt}"));
        return Command::SUCCESS;
    }
}
