<?php

declare(strict_types=1);

namespace Lion\Bundle\Interface;

/**
 * Define an HTML template
 *
 * @package Lion\Bundle\Interface
 */
interface HtmlInterface
{
    /**
     * Define the HTML template
     *
     * @return void
     */
    public function template(): HtmlInterface;
}
