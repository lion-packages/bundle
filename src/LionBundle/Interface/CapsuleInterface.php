<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * Implement abstract methods for capsule classes
 *
 * @package Lion\Bundle\Interface
 */
interface CapsuleInterface
{
    /**
     * Returns an object of the class
     *
     * @return object
     * */
    public function capsule(): object;
}
