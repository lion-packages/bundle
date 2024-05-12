<?php

declare(strict_types=1);

namespace Tests\Helpers\Commands;

use Lion\Bundle\Helpers\Commands\Html;
use Lion\Files\Store;
use Lion\Test\Test;

class HtmlTest extends Test
{
    const REPLACE_TEXT = 'Lion-Bundle';

    private Html $html;

    protected function setUp(): void
    {
        $this->html = new Html();

        $this->initReflection($this->html);
    }

    public function testAdd(): void
    {
        $template = (new Store)->get('./tests/Providers/Helpers/Commands/HtmlTemplate.html');

        $this->getPrivateMethod('add', [$template]);

        $this->assertSame($template, $this->getPrivateProperty('htmlTemplate'));
    }

    public function testReplace(): void
    {
        $template = (new Store)->get('./tests/Providers/Helpers/Commands/HtmlTemplate.html');

        $this->getPrivateMethod('add', [$template]);

        $this->assertSame($template, $this->getPrivateProperty('htmlTemplate'));

        $this->html->replace('REPLACE', self::REPLACE_TEXT);

        $templateReplaced = (new Store)->get('./tests/Providers/Helpers/Commands/HtmlTemplateReplaced.html');

        $this->assertSame($templateReplaced, $this->getPrivateProperty('htmlTemplate'));
    }

    public function testGet(): void
    {
        $template = (new Store)->get('./tests/Providers/Helpers/Commands/HtmlTemplate.html');

        $this->getPrivateMethod('add', [$template]);

        $this->assertSame($template, $this->getPrivateProperty('htmlTemplate'));

        $this->html->replace('REPLACE', self::REPLACE_TEXT);

        $templateReplaced = (new Store)->get('./tests/Providers/Helpers/Commands/HtmlTemplateReplaced.html');

        $this->assertSame($templateReplaced, $this->html->get());
    }
}
