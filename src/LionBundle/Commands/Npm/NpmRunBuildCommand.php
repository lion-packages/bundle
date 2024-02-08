<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmRunBuildCommand as LionNpmRunBuildCommand;

class NpmRunBuildCommand extends LionNpmRunBuildCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('build');
    }
}
