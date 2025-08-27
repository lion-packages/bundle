<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

use JsonSerializable;
use Lion\Database\Interface\DatabaseCapsuleInterface;

/**
 * Defines the contract for capsule classes that represent entities or data
 * containers in the application.
 *
 * Extends:
 * - JsonSerializable: requires implementing jsonSerialize() to define how the
 * object should be converted to JSON.
 * - DatabaseCapsuleInterface: requires implementing methods related to persistence
 * or database interactions.
 */
interface CapsuleInterface extends JsonSerializable, DatabaseCapsuleInterface
{
    /**
     * Returns an instance of the implementing class.
     *
     * Typically, this method is used to hydrate the object by invoking its setter
     * methods and assigning values to its properties. The method then returns the
     * same object instance for further use.
     *
     * @return self The hydrated instance of the capsule class.
     */
    public function capsule(): self;
}
