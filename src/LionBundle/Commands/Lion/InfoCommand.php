<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Shows the libraries installed in the project in a table
 *
 * @property Arr $arr [Arr class object]
 * @property ComposerFactory $composerFactory [ComposerFactory class object]
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
    const EXTENSIONS = [
        'php',
        'ext-ctype',
        'ext-filter',
        'ext-hash',
        'ext-mbstring',
        'ext-openssl',
        'ext-session',
        'ext-tokenizer'
    ];

    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * [ComposerFactory class object]
     *
     * @var ComposerFactory $composerFactory
     */
    private ComposerFactory $composerFactory;

    /**
     * @required
     * */
    public function setArr(Arr $arr): InfoCommand
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * @required
     */
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
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $composerJson = json_decode(file_get_contents("composer.json"));

        $libraries = $this->composerFactory
            ->libraries($composerJson, self::EXTENSIONS)
            ->librariesDev($composerJson, self::EXTENSIONS)
            ->getLibraries();

        $size = $this->arr->of($libraries)->length();

        (new Table($output))
            ->setHeaderTitle($this->successOutput(' LIBRARIES '))
            ->setHeaders(['LIBRARY', 'VERSION', 'LICENSE', 'DEV', 'DESCRIPTION'])
            ->setFooterTitle(
                $size > 1
                    ? $this->successOutput(" Showing [{$size}] libraries ")
                    : ($size === 1
                        ? $this->successOutput(" showing a single library ")
                        : $this->successOutput(" No libraries available ")
                    )
            )
            ->setRows($libraries)
            ->render();

        return Command::SUCCESS;
    }
}
