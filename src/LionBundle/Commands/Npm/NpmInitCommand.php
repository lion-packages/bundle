<?php

declare(strict_types=1);

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmInitCommand as LionNpmInitCommand;

/**
 * Initialize a project with Vite.JS
 *
 * @property ClassFactory $classFactory [ClassFactory class object]
 * @property FileWriter $fileWriter [FileWriter class object]
 * @property Kernel $Kernel [kernel class object]
 *
 * @package Lion\Bundle\Commands\Lion\Npm
 */
class NpmInitCommand extends LionNpmInitCommand
{
    /**
     * Configures the current command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('init');
    }
}
