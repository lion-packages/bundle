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

    private function increment(array $row, int $increment): int
    {
        if ($this->str->of($row['content'])->contains(["\n"]) !== false) {
            $increment += (substr_count($row['content'], "\n") - 1);
        }

        return $increment;
    }

    private function replaceContent(array $row, string $modifiedLine, string $originalLine): string
    {
        if ($row['search'] === '--all-elem--') {
            $modifiedLine = str_pad($row['content'], strlen($originalLine));
        } else {
            $newLine = $this->str->of($originalLine)->replace($row['search'], $row['content'])->get();
            $modifiedLine = str_pad($newLine, strlen($originalLine));
        }

        return $modifiedLine;
    }

    public function readFileRows(string $path, array $rows): void
    {
        $increment = 0;

        foreach ($rows as $key => $row) {
            $file = fopen($path, 'r+');
            $rowsFile = file($path);

            if ($key >= 1 && $key <= count($rowsFile)) {
                $total = ($key - 1) + $increment;
                $originalLine = $rowsFile[$total];
                $modifiedLine = '';

                if ($row['replace'] === false) {
                    $modifiedLine = str_pad($row['content'], strlen($originalLine));
                    $increment = $this->increment($row, $increment);
                } else {
                    if (isset($row['multiple'])) {
                        foreach ($row['multiple'] as $key => $content) {
                            $modifiedLine = $this->replaceContent(
                                $content,
                                $modifiedLine,
                                ($key === 0 ? $originalLine : $modifiedLine)
                            );

                            $increment = $this->increment($content, $increment);
                        }
                    } else {
                        $modifiedLine = $this->replaceContent($row, $modifiedLine, $originalLine);
                        $increment = $this->increment($row, $increment);
                    }
                }

                fseek($file, 0);
                for ($i = 0; $i < count($rowsFile); $i++) {
                    if ($i == $total) {
                        fwrite($file, $modifiedLine);
                    } else {
                        fwrite($file, $rowsFile[$i]);
                    }
                }
            }

            fclose($file);
        }
    }
}
