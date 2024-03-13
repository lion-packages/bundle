<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmUninstallCommand as LionNpmUninstallCommand;

/**
 * Uninstall the Vite.JS project dependencies
 *
 * @property Kernel $Kernel [kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmUninstallCommand extends LionNpmUninstallCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('uninstall');
    }
}
