<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Lion;

use Lion\Bundle\Helpers\Commands\ComposerFactory;
use Lion\Command\Command;
use Lion\Helpers\Arr;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InfoCommand extends Command
{
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

    private Arr $arr;
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

    protected function configure(): void
    {
        $this
            ->setName('info')
            ->setDescription('Command to display basic project information and libraries');
    }

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
