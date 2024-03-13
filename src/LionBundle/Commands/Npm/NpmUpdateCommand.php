<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmUpdateCommand as LionNpmUpdateCommand;

/**
 * Update Vite.JS project dependencies
 *
 * @property Kernel $Kernel [kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmUpdateCommand extends LionNpmUpdateCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('update');
    }
}
