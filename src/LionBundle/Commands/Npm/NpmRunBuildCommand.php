<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmRunBuildCommand as LionNpmRunBuildCommand;

/**
 * Generate the dist of the Vite.JS project
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmRunBuildCommand extends LionNpmRunBuildCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('build');
    }
}
