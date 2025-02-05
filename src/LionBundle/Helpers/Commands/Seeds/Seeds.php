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
        /** @var array<string, SeedInterface> $seeds */
        $seeds = [];

        foreach ($list as $namespace) {
            /** @var SeedInterface $classObject */
            $classObject = new $namespace();

            $seeds[$namespace] = $classObject;
        }

        /**
         * @param array<string, SeedInterface> $list
         *
         * @return void
         */
        $run = function (array $list): void {
            foreach ($list as $seed) {
                if ($seed instanceof SeedInterface) {
                    $seed->run();
                }
            }
        };

        /** @var array<string, SeedInterface> $orderSeeds */
        $orderSeeds = $this->migrations->orderList($seeds);

        $run($orderSeeds);
    }
}
