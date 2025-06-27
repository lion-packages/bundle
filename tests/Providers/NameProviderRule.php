<?php

declare(strict_types=1);

namespace Tests\Providers;

use Lion\Route\Helpers\Rules;
use Lion\Route\Interface\RulesInterface;
use Valitron\Validator;

/**
 * @package Tests\Providers
 */
class NameProviderRule extends Rules implements RulesInterface
{
    /**
     * [field for 'name']
     *
     * @var string $field
     */
    public string $field = 'name';

    /**
     * [description for 'name']
     *
     * @var string $desc
     */
    public string $desc = 'name';

    /**
     * [value for 'name']
     *
     * @var string $value;
     */
    public string $value = 'name';

    /**
     * [Defines whether the column is optional for postman collections]
     *
     * @var bool $disabled;
     */
    public bool $disabled = false;

    /**
     * {@inheritDoc}
     */
    public function passes(): void
    {
        $this->validate(function (Validator $validator): void {
            $validator
                ->rule('required', $this->field)
                ->message('the "name" property is required');
        });
    }
}
