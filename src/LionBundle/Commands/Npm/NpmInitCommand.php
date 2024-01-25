<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand as NpmNpmInitCommand;

class NpmInitCommand extends NpmNpmInitCommand
{
    protected function configure(): void
    {
        parent::configure();

        $this->setName('init');
    }
}
