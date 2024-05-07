<?php

declare(strict_types=1);

namespace Lion\Bundle\Exceptions;

use Exception;
use JsonSerializable;

/**
 * Description of 'ExampleException'
 *
 * @package Lion\Bundle\Exceptions
 */
class RulesException extends Exception implements JsonSerializable
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): mixed
    {
        $json = json_decode($this->getMessage());

        return error($json->message, $this->getCode(), $json->data);
    }
}
