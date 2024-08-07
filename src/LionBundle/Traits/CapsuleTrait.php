<?php

declare(strict_types=1);

namespace Lion\Bundle\Traits;

trait CapsuleTrait
{
    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName(): string
    {
        return $this->entity;
    }
}
