<?php

declare(strict_types=1);

namespace LionBundle\Commands\Token;

use LionCommand\Command;
use LionSecurity\JWT;
use LionSecurity\RSA;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateJWTCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('token:jwt')
            ->setDescription('Created command to generate JWT token')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Save to a specific path?')
            ->addOption('session', 's', InputOption::VALUE_OPTIONAL, 'Session must be true or false', true);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = $input->getOption("path");
        RSA::setPath($path != null ? $path : RSA::getPath());

        $session = $input->getOption('session');
        $session = true === $session ? true : $session;
        $session = 'true' === $session ? true : false;

        $jwt = JWT::encode([
            'session' => $session,
            'system' => 'Lion-Framework',
            'autor' => 'Sergio Leon',
            'github' => 'https://github.com/lion-packages'
        ]);

        $output->writeln($this->warningOutput("\t>>  TOKEN: JWT created successfully"));
        $output->writeln($this->successOutput("\t>>  TOKEN: {$jwt->data->jwt}"));
        return Command::SUCCESS;
    }
}
