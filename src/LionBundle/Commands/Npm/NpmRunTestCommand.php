<?php

namespace Lion\Bundle\Commands\Npm;

use Lion\Bundle\Commands\Lion\Npm\NpmRunTestCommand as LionNpmRunTestCommand;

/**
 * Run the tests using NPM.
 */
class NpmRunTestCommand extends LionNpmRunTestCommand
{
    /**
     * Configures the current command.
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->setName('test');
    }
}
