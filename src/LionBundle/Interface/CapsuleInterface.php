<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use JsonSerializable;
use Lion\Database\Interface\DatabaseCapsuleInterface;

/**
 * Implement abstract methods for capsule classes
 *
 * @package Lion\Bundle\Interface
 */
interface CapsuleInterface extends JsonSerializable, DatabaseCapsuleInterface
{
    /**
     * Returns an object of the class
     *
     * @return CapsuleInterface
     */
    public function capsule(): CapsuleInterface;
}
