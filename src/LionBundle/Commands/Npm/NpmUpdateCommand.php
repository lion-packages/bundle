<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmUpdateCommand as LionNpmUpdateCommand;

class NpmUpdateCommand extends LionNpmUpdateCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('update');
    }
}
