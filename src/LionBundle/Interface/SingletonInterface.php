<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * The SingletonInterface is an interface in PHP designed to mark or identify
 * that a class implements the Singleton design pattern. This interface acts as
 * a contract indicating that the classes that implement it must follow the
 * rules and behaviors of the Singleton pattern. Its main purpose is to serve as
 * an identifier for Singleton classes within the project
 *
 * @package Lion\Bundle\Interface
 */
interface SingletonInterface
{
    /**
     * Static method to get the unique instance of the class
     *
     * @return SingletonInterface
     */
    public static function getInstance(): SingletonInterface;
}
