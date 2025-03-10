<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

/**
 * Adds the abstract methods of the Capsule interface
 *
 * @package Lion\Bundle\Traits
 */
trait CapsuleTrait
{
    /**
     * {@inheritDoc}
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritDoc}
     */
    public static function getTableName(): string
    {
        return self::$entity;
    }
}
