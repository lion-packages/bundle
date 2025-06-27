<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

/**
 * This trait provides an implementation of the Singleton design pattern,
 * ensuring that a class has only one instance and provides a global access
 * point to it
 *
 * @package Lion\Bundle\Traits
 */
trait SingletonTrait
{
    /**
     * [Holds the unique instance of the class]
     *
     * @var self|null $singleton
     */
    private static ?self $singleton = null;

    /**
     * Private and final constructor to prevent creating multiple instances
     */
    final private function __construct()
    {
        $this->init();
    }

    /**
     * {@inheritDoc}
     */
    final public static function getInstance(): self
    {
        if (self::$singleton === null) {
            self::$singleton = new self();
        }

        return self::$singleton;
    }

    /**
     * Protected method that can be overridden by the class using the trait.
     * Called in the constructor
     *
     * @return void
     */
    protected function init(): void
    {
    }
}
