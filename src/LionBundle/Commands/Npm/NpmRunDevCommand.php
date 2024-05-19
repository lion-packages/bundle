<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmRunDevCommand as NpmNpmRunDevCommand;

/**
 * Run the local vite environment for development
 *
 * @package Lion\Bundle\Commands\Npm
 */
class NpmRunDevCommand extends NpmNpmRunDevCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('dev');
    }
}
