<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Lion\Command\Command;
use Lion\Security\AES;
use LogicException;
use stdClass;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Generates the necessary configuration for symmetric encryption with AES
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
    private const array AES_METHODS = [
        AES::AES_256_CBC,
    ];

    /**
     * [It allows you to generate the configuration required for AES encryption
     * and decryption, it has methods that allow you to encrypt and decrypt data
     * with AES]
     *
     * @var AES $aes
     */
    private AES $aes;

    #[Inject]
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
     * @return int
     *
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $aesMethods = [
            ...self::AES_METHODS,
        ];

        $default = reset($aesMethods);

        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $choiseQuestion = new ChoiceQuestion(
            ('Select AES method ' . $this->warningOutput("(default: '{$default}')")),
            $aesMethods,
            0
        );

        /** @var string $aesMethod */
        $aesMethod = $helper->ask($input, $output, $choiseQuestion);

        /** @var stdClass $config */
        $config = $this->aes
            ->create($aesMethod)
            ->toObject()
            ->get();

        /** @var string $passphrase */
        $passphrase = $config->passphrase;

        /** @var string $key */
        $key = $config->key;

        /** @var string $iv */
        $iv = $config->iv;

        $output->writeln($this->infoOutput("\t>>  AES METHOD: {$aesMethod}"));

        $output->writeln($this->warningOutput("\t>>  AES PASSPHRASE: {$passphrase}"));

        $output->writeln($this->warningOutput("\t>>  AES KEY: {$key}"));

        $output->writeln($this->warningOutput("\t>>  AES IV: {$iv}"));

        $output->writeln($this->successOutput("\t>>  Keys created successfully"));

        return parent::SUCCESS;
    }
}
