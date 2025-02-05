<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use DI\Attribute\Inject;
use Lion\Helpers\Str;

/**
 * Class that allows writing system files
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

    #[Inject]
    public function setStr(Str $str): void
    {
        $this->str = $str;
    }

    /**
     * Replaces the content of a string with another
     *
     * @param array{
     *     search: string,
     *     content: string
     * } $row [Row to modify]
     * @param string $originalLine [Original row content]
     *
     * @return string
     */
    private function replaceContent(array $row, string $originalLine): string
    {
        /** @var string $newLine */
        $newLine = $this->str
            ->of($originalLine)
            ->replace($row['search'], $row['content'])
            ->get();

        return str_pad($newLine, strlen($originalLine));
    }

    /**
     * Reads all rows from a file and modifies them as defined
     *
     * @param string $path [Defined route]
     * @param array<int, array{
     *     replace?: bool,
     *     remove?: bool,
     *     content?: string,
     *     search?: string,
     *     multiple?: array<int, array{
     *         content?: string,
     *         search?: string
     *     }>
     * }> $rows [list of rows to modify]
     *
     * @return void
     */
    public function readFileRows(string $path, array $rows): void
    {
        /** @var resource $file */
        $file = fopen($path, 'r+');

        /** @var list<string> $rowsFile */
        $rowsFile = file($path);

        /**
         * @var array{
         *     replace?: bool,
         *     remove?: bool,
         *     content?: string,
         *     search?: string,
         *     multiple?: array<int, array{
         *         content?: string,
         *         search?: string
         *     }>
         * } $row
         */
        foreach ($rows as $key => $row) {
            if ($key >= 1 && $key <= count($rowsFile)) {
                fseek($file, 0);

                $total = $key - 1;

                $modifiedLine = '';

                if (isset($row['remove'])) {
                    unset($rowsFile[$total]);
                } else {
                    $originalLine = $rowsFile[$total];

                    if (isset($row['replace'], $row['content']) && !$row['replace']) {
                        $modifiedLine = str_pad($row['content'], strlen($originalLine));
                    }

                    if (isset($row['replace']) && $row['replace']) {
                        if (isset($row['multiple'])) {
                            /**
                             * @var array{
                             *     search: string,
                             *     content: string
                             * } $content
                             */
                            foreach ($row['multiple'] as $content) {
                                $originalLine = $this->replaceContent($content, $originalLine);
                            }

                            $modifiedLine = $originalLine;
                        } else {
                            if (isset($row['search'], $row['content'])) {
                                $modifiedLine = $this->replaceContent([
                                    'search' => $row['search'],
                                    'content' => $row['content'],
                                ], $originalLine);
                            }
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
