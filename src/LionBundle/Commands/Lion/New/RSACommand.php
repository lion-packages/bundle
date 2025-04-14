<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion\New;

use DI\Attribute\Inject;
use Exception;
use Lion\Command\Command;
use Lion\Files\Store;
use Lion\Security\RSA;
use LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generate public and private keys with RSA
 *
 * @package Lion\Bundle\Commands\Lion\New
 */
class RSACommand extends Command
{
    /**
     * [Allows you to generate the required configuration for public and private
     * keys, has methods that allow you to encrypt and decrypt data with RSA]
     *
     * @var RSA $rsa
     */
    private RSA $rsa;

    /**
     * [Manipulate system files]
     *
     * @var Store $store
     */
    private Store $store;

    #[Inject]
    public function setRSA(RSA $rsa): RSACommand
    {
        $this->rsa = $rsa;

        return $this;
    }

    #[Inject]
    public function setStore(Store $store): RSACommand
    {
        $this->store = $store;

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
            ->setName('new:rsa')
            ->setDescription('Command required to create public and private keys for RSA encryptions')
            ->addOption('path', 'p', InputOption::VALUE_REQUIRED, 'Save to a specific path?');
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
     * @throws Exception
     * @throws LogicException [When this abstract method is not implemented]
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string|null $path */
        $path = $input->getOption('path');

        /** @var string $rsaPrivateKeyBits */
        $rsaPrivateKeyBits = env('RSA_PRIVATE_KEY_BITS');

        /** @var string $rsaPath */
        $rsaPath = env('RSA_PATH');

        /** @var string $rsaDefaultMd */
        $rsaDefaultMd = env('RSA_DEFAULT_MD');

        $this->rsa->config([
            'urlPath' => null === $path ? $this->rsa->getUrlPath() : storage_path($path),
            'rsaConfig' => $rsaPath,
            'rsaPrivateKeyBits' => (int) $rsaPrivateKeyBits,
            'rsaDefaultMd' => $rsaDefaultMd,
        ]);

        $urlPath = $this->rsa->getUrlPath();

        $this->store->folder($urlPath);

        $this->rsa->create();

        $output->writeln($this->warningOutput("\t>>  RSA KEYS: public and private"));

        $output->writeln($this->successOutput("\t>>  RSA KEYS: Exported in {$urlPath}public.key"));

        $output->writeln($this->successOutput("\t>>  RSA KEYS: Exported in {$urlPath}private.key"));

        return parent::SUCCESS;
    }
}
