<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use LogicException;
use stdClass;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shows the libraries installed in the project in a table
 *
 * @package Lion\Bundle\Commands\Lion
 */
class InfoCommand extends Command
{
    /**
     * [List of ignored extensions]
     *
     * @const EXTENSIONS
     */
    private const array EXTENSIONS = [
        'php',
        'ext-ctype',
        'ext-filter',
        'ext-hash',
        'ext-mbstring',
        'ext-openssl',
        'ext-session',
        'ext-tokenizer',
    ];

    /**
     * [Modify and build arrays with different indexes or values]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * [Gets the list of installed libraries and dev-libraries]
     *
     * @var ComposerFactory $composerFactory
     */
    private ComposerFactory $composerFactory;

    #[Inject]
    public function setArr(Arr $arr): InfoCommand
    {
        $this->arr = $arr;

        return $this;
    }

    #[Inject]
    public function setComposerFactory(ComposerFactory $composerFactory): InfoCommand
    {
        $this->composerFactory = $composerFactory;

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
            ->setName('info')
            ->setDescription('Command to display basic project information and libraries');
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
        /** @var string $file */
        $file = file_get_contents("composer.json");

        /** @var stdClass $composerJson */
        $composerJson = json_decode($file);

        $libraries = $this->composerFactory
            ->libraries($composerJson, self::EXTENSIONS)
            ->librariesDev($composerJson, self::EXTENSIONS)
            ->getLibraries();

        $size = $this->arr
            ->of($libraries)
            ->length();

        new Table($output)
            ->setHeaderTitle('<fg=green> LIBRARIES </>')
            ->setHeaders(['LIBRARY', 'VERSION', 'LICENSE', 'DEV', 'DESCRIPTION'])
            ->setFooterTitle("<fg=green> Showing [{$size}] libraries </>")
            ->setRows($libraries)
            ->render();

        return parent::SUCCESS;
    }
}
