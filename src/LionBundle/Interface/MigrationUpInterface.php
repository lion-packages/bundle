<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

interface MigrationUpInterface
{
    /**
     * Run the process and create a table in the database
     * */
    public function up(): object;
}
