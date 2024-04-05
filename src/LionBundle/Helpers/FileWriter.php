<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Lion\DependencyInjection\Container;
use Lion\Helpers\Str;

/**
 * Class that allows writing system files
 *
 * @property Str $str [Str class object]
 * @property Container $container [Container class object]
 *
 * @package Lion\Bundle\Helpers
 */
class FileWriter
{
    /**
     * [Object of class Str]
     *
     * @var Str $str
     */
    private Str $str;

    /**
     * [Object of class Container]
     *
     * @var Container $container
     */
    private Container $container;

    /**
     * @required
     */
    public function setStr(Str $str)
    {
        $this->str = $str;
    }

    /**
     * @required
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Replaces the content of a string with another
     *
     * @param array $row [Row to modify]
     * @param string $modifiedLine [Modified row content]
     * @param string $originalLine [Original row content]
     *
     * @return string
     */
    private function replaceContent(array $row, string $modifiedLine, string $originalLine): string
    {
        $newLine = $this->str->of($originalLine)->replace($row['search'], $row['content'])->get();

        $modifiedLine = str_pad($newLine, strlen($originalLine));

        return $modifiedLine;
    }

    /**
     * Reads all rows from a file and modifies them as defined
     *
     * @param string $path [Defined route]
     * @param array $rows [list of rows to modify]
     *
     * @return void
     */
    public function readFileRows(string $path, array $rows): void
    {
        $path = $this->container->normalizePath($path);

        $file = fopen($path, 'r+');

        $rowsFile = file($path);

        foreach ($rows as $key => $row) {
            if ($key >= 1 && $key <= count($rowsFile)) {
                fseek($file, 0);

                if (isset($row['remove'])) {
                    $total = $key - 1;

                    unset($rowsFile[$total]);
                } else {
                    $total = $key - 1;

                    $originalLine = $rowsFile[$total];

                    $modifiedLine = '';

                    if ($row['replace'] === false) {
                        $modifiedLine = str_pad($row['content'], strlen($originalLine));
                    } else {
                        if (isset($row['multiple'])) {
                            foreach ($row['multiple'] as $key => $content) {
                                $originalLine = $this->replaceContent(
                                    $content,
                                    ($key === 0 ? $originalLine : $modifiedLine),
                                    $originalLine
                                );
                            }

                            $modifiedLine = $originalLine;
                        } else {
                            $modifiedLine = $this->replaceContent($row, $modifiedLine, $originalLine);
                        }
                    }

                    $rowsFile[$total] = $modifiedLine;
                }
            }
        }

        ftruncate($file, 0);

        fwrite($file, implode('', $rowsFile));

        fclose($file);
    }
}
