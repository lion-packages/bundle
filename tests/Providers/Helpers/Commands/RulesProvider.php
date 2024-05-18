<?php

declare(strict_types=1);

namespace Tests\Providers\Helpers\Commands;

use Lion\Bundle\Helpers\Rules;
use Lion\Bundle\Interface\RulesInterface;
use Valitron\Validator;

class RulesProvider extends Rules implements RulesInterface
{
    public string $field = 'name';

    public string $desc = '';

    public string $value = '';

    public bool $disabled = false;

    public function passes(): void
    {
        $this->validate(function (Validator $validator): void {
            $validator
                ->rule('optional', $this->field)
                ->message('the "name" property is optional');
        });
    }
}
