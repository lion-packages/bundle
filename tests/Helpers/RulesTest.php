<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Closure;
use Lion\Bundle\Helpers\Rules;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use Valitron\Validator;

class RulesTest extends Test
{
    private object $rules;

    protected function setUp(): void
    {
        $this->rules = (new Container())
            ->injectDependencies(
                new class extends Rules
                {
                    public function validate(Closure $validateFunction): void
                    {
                        parent::validate($validateFunction);
                    }
                }
            );

        $this->initReflection($this->rules);
    }

    public function testValidate(): void
    {
        $_POST['id'] = '';

        $this->rules->validate(function (Validator $validator): void {
            $validator
                ->rule('required', 'id')
                ->message('custom message');
        });

        $errors = $this->getPrivateProperty('responses');

        $this->assertIsArray($errors);
        $this->assertArrayHasKey('id', $errors);
        $this->assertSame(['id' => ['custom message']], $errors);
    }

    public function testGetErrors(): void
    {
        $_POST['id'] = '';

        $this->rules->validate(function (Validator $validator): void {
            $validator
                ->rule('required', 'id')
                ->message('custom message');
        });

        $errors = $this->rules->getErrors();

        $this->assertIsArray($errors);
        $this->assertArrayHasKey('id', $errors);
        $this->assertSame(['id' => ['custom message']], $errors);
    }
}
