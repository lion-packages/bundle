<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands\Seeds;

use DI\Attribute\Inject;
use Lion\Bundle\Helpers\Commands\Migrations\Migrations;
use Lion\Bundle\Interface\SeedInterface;

/**
 * Manages the processes of creating or executing seeds
 *
 * @property Migrations $migrations [Manages the processes of creating or
 * executing migrations]
 *
 * @package Lion\Bundle\Helpers\Commands\Seeds
 */
class Seeds
{
    /**
     * [Manages the processes of creating or executing migrations]
     *
     * @var Migrations $migrations
     */
    private Migrations $migrations;

    #[Inject]
    public function setMigrations(Migrations $migrations): Seeds
    {
        $this->migrations = $migrations;

        return $this;
    }

    /**
     * Run the migrations
     *
     * @param array<int, string> $list [List of classes]
     *
     * @return void
     */
    public function executeSeedsGroup(array $list): void
    {
        /** @var array<string, SeedInterface> $migrations */
        $seeds = [];

        foreach ($list as $namespace) {
            /** @var SeedInterface $classObject */
            $classObject = new $namespace();

            $seeds[$namespace] = $classObject;
        }

        $execute = function (SeedInterface $seed, string $namespace): void {
            $response = $seed->run();

            echo("\033[0;33m\t>> SEED: {$namespace}\033[0m\n");

            if (isError($response)) {
                echo("\033[0;31m\t>> SEED: {$response->message}\033[0m\n");
            } else {
                echo("\033[0;32m\t>> SEED: {$response->message}\033[0m\n");
            }
        };

        $run = function (array $list) use ($execute): void {
            foreach ($list as $namespace => $seed) {
                $execute($seed, $namespace);
            }
        };

        $run($this->migrations->orderList($seeds));

        echo("\n\033[0;36m\t>> Seed group executed successfully\033[0m \n");
    }
}
