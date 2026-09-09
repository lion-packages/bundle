<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmUninstallCommand as LionNpmUninstallCommand;

/**
 * Uninstall project dependencies using NPM.
 */
class NpmUninstallCommand extends LionNpmUninstallCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('uninstall');
    }
}
