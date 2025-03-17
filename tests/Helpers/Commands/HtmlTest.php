<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\Html;
use Lion\Files\Store;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;

class HtmlTest extends Test
{
    private const string REPLACE_TEXT = 'Lion-Bundle';

    private Html $html;
    private Store $store;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $this->html = new Html();

        $this->store = new Store();

        $this->initReflection($this->html);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function add(): void
    {
        $template = $this->store->get('./tests/Providers/Helpers/Commands/HtmlTemplate.html');

        $this->getPrivateMethod('add', [
            'htmlTemplate' => $template,
        ]);

        $this->assertSame($template, $this->getPrivateProperty('htmlTemplate'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function replace(): void
    {
        $template = $this->store->get('./tests/Providers/Helpers/Commands/HtmlTemplate.html');

        $this->getPrivateMethod('add', [
            'htmlTemplate' => $template,
        ]);

        $this->assertSame($template, $this->getPrivateProperty('htmlTemplate'));

        $this->html->replace('--REPLACE--', self::REPLACE_TEXT);

        $templateReplaced = $this->store->get('./tests/Providers/Helpers/Commands/HtmlTemplateReplaced.html');

        $this->assertSame($templateReplaced, $this->getPrivateProperty('htmlTemplate'));
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    public function get(): void
    {
        $template = $this->store->get('./tests/Providers/Helpers/Commands/HtmlTemplate.html');

        $this->getPrivateMethod('add', [
            'htmlTemplate' => $template,
        ]);

        $this->assertSame($template, $this->getPrivateProperty('htmlTemplate'));

        $this->html->replace('--REPLACE--', self::REPLACE_TEXT);

        $templateReplaced = $this->store->get('./tests/Providers/Helpers/Commands/HtmlTemplateReplaced.html');

        $this->assertSame($templateReplaced, $this->html->get());
    }
}
