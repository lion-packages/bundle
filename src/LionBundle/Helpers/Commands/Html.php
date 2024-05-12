<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

/**
 * Allows you to process and transform an HTML template
 *
 * @property string $htmlTemplate [Contains the current HTML template]
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class Html
{
    /**
     * [Contains the current HTML template]
     *
     * @var string $htmlTemplate
     */
    private string $htmlTemplate;

    /**
     * Defines the current HTML template
     *
     * @param string $htmlTemplate [Contains the current HTML template]
     *
     * @return void
     */
    protected function add(string $htmlTemplate): void
    {
        $this->htmlTemplate = $htmlTemplate;
    }

    /**
     * Replaces values defined in the HTML template
     *
     * @param string $search [Text searched to be replaced]
     * @param string $replace [Text to replace string with another string]
     *
     * @return Html
     */
    public function replace(string $search, string $replace): Html
    {
        $search = "{{ " . trim($search) . " }}";

        $this->htmlTemplate = str_replace($search, $replace, $this->htmlTemplate);

        return $this;
    }

    /**
     * Gets the current HTML template
     *
     * @return string
     */
    public function get(): string
    {
        return $this->htmlTemplate;
    }
}
