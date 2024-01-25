<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInstallCommand as LionNpmInstallCommand;

class NpmInstallCommand extends LionNpmInstallCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('install');
    }
}
