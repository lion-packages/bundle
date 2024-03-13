<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use JsonSerializable;

/**
 * Implement abstract methods for capsule classes
 *
 * @package Lion\Bundle\Interface
 */
interface CapsuleInterface extends JsonSerializable
{
    /**
     * Returns an object of the class
     *
     * @return object
     * */
    public function capsule(): object;
}
