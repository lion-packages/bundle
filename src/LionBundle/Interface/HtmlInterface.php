<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * Define an HTML template.
 */
interface HtmlInterface
{
    /**
     * Define the HTML template.
     *
     * @return self
     */
    public function template(): self;
}
