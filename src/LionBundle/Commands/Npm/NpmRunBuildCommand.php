<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmRunBuildCommand as LionNpmRunBuildCommand;

/**
 * Generates the project's distribution folder (dist).
 */
class NpmRunBuildCommand extends LionNpmRunBuildCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('build');
    }
}
