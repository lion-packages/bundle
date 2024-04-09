<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use Lion\Command\Command;
use Lion\Security\AES;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Generates the necessary configuration for symmetric encryption with AES
 *
 * @property AES $aes [AES class object]
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class AESCommand extends Command
{
    /**
     * [List of available AES methods]
     *
     * @const array AES_METHODS
     */
    const AES_METHODS = [AES::AES_256_CBC];

    /**
     * [AES class object]
     *
     * @var AES $aes
     */
    private AES $aes;

    /**
     * @required
     */
    public function setAES(AES $aes): AESCommand
    {
        $this->aes = $aes;

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
            ->setName('new:aes')
            ->setDescription("Command required to create 'KEY' and 'IV' keys for AES encryptions");
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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $aesMethods = [...self::AES_METHODS];

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $aesMethod = $helper->ask(
            $input,
            $output,
            new ChoiceQuestion(
                ('Select AES method ' . $this->warningOutput('(default: ' . reset($aesMethods) . ')')),
                $aesMethods,
                0
            )
        );

        $config = $this->aes->create($aesMethod)->toObject()->get();

        $output->writeln($this->infoOutput("\t>>  AES METHOD: {$aesMethod}"));
        $output->writeln($this->warningOutput("\t>>  AES KEY: {$config->key}"));
        $output->writeln($this->warningOutput("\t>>  AES IV: {$config->iv}"));
        $output->writeln($this->successOutput("\t>>  Keys created successfully"));

        return Command::SUCCESS;
    }
}
