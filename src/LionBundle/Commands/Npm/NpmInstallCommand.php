<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand as LionNpmInstallCommand;

/**
 * Install the project dependencies using NPM.
 */
class NpmInstallCommand extends LionNpmInstallCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('install');
    }
}
