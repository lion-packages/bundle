<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use DI\Attribute\Inject;
use Lion\Command\Command;
use Lion\Security\Validation;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate a secure hash
 *
 * @codeCoverageIgnore
 */
class HashCommand extends Command
{
    /**
     * [Validation class object]
     *
     * @var Validation $validation
     */
    private Validation $validation;

    #[Inject]
    public function setValidation(Validation $validation): HashCommand
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('hash')
            ->setDescription('Generate a secure HASH for the server');
    }

    /**
     * Executes the current command
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method
     *
     * @param InputInterface $input [InputInterface is the interface implemented
     * by all input classes]
     * @param OutputInterface $output [OutputInterface is the interface
     * implemented by all Output classes]
     *
     * @return int
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln($this->successOutput("\t>>  HASH: {$this->validation->sha256(uniqid())}"));

        return Command::SUCCESS;
    }
}
