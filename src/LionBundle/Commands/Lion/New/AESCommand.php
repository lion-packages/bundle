<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Command\Command;
use Lion\Security\AES;
use LogicException;
use stdClass;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates the necessary configuration for symmetric encryption with AES.
 */
class AESCommand extends Command
{
    /**
     * It allows you to generate the configuration required for AES encryption and
     * decryption, it has methods that allow you to encrypt and decrypt data with
     * AES.
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
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('new:aes')
            ->setDescription("Command required to create 'KEY' and 'IV' keys for AES encryptions.");
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class as a concrete
     * class. In this case, instead of defining the execute() method, you set the
     * code to execute by passing a Closure to the setCode() method.
     *
     * @param InputInterface $input InputInterface is the interface implemented by
     * all input classes.
     * @param OutputInterface $output OutputInterface is the interface implemented
     * by all Output classes.
     *
     * @return int
     *
     * @throws Exception If the algorithm is not supported.
     * @throws LogicException When this abstract method is not implemented.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var stdClass $config */
        $config = $this->aes
            ->create()
            ->toObject()
            ->get();

        /** @var string $key */
        $key = $config->key;

        $output->writeln($this->infoOutput("\t>>  AES METHOD: " . AES::AES_256_GCM));

        $output->writeln($this->warningOutput("\t>>  AES KEY: {$key}"));

        $output->writeln($this->successOutput("\t>>  Keys created successfully"));

        return parent::SUCCESS;
    }
}
