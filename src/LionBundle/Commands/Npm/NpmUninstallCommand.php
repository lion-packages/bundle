<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmUninstallCommand as LionNpmUninstallCommand;

class NpmUninstallCommand extends LionNpmUninstallCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('uninstall');
    }
}
