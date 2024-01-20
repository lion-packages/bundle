<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

interface MigrationRunInterface
{
    /**
     * Run the process and add the rows to the table
     * */
    public function run(): array;
}
