<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers;

use Lion\Helpers\Str;

class FileWriter
{
    private Str $str;

    public function __construct()
    {
        $this->str = new Str();
    }

    private function replaceContent(array $row, string $modifiedLine, string $originalLine): string
    {
        if ('--all-elem--' === $row['search']) {
            $modifiedLine = str_pad($row['content'], strlen($originalLine));
        } else {
            $newLine = $this->str->of($originalLine)->replace($row['search'], $row['content'])->get();
            $modifiedLine = str_pad($newLine, strlen($originalLine));
        }

        return $modifiedLine;
    }

    public function readFileRows(string $path, array $rows): void
    {
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
