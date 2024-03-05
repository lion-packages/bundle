<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Helpers\Arr;

class ComposerFactory
{
    /**
     * [Object of class Arr]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    private array $libraries = [];

    /**
     * @required
     */
    public function setArr(Arr $arr): ComposerFactory
    {
        $this->arr = $arr;

        return $this;
    }

    public function libraries(object $composerJson, array $extensions): ComposerFactory
    {
        foreach ($composerJson->require as $key => $library) {
            if (!in_array($key, $extensions, true)) {
                $exec = [];
                exec("composer show {$key} --direct --format=json", $exec);
                $json = json_decode($this->arr->of($exec)->join(' '));

                $this->libraries[] = [
                    $key,
                    "\033[0;33m{$json->versions[0]}\033[0m",
                    "\033[0;33m{$json->licenses[0]->osi}\033[0m",
                    "\033[0;31mfalse\033[0m",
                    $json->description
                ];
            }
        }

        return $this;
    }

    public function librariesDev(object $composerJson, array $extensions): ComposerFactory
    {
        foreach ($composerJson->{'require-dev'} as $key => $library) {
            if (!in_array($key, $extensions, true)) {
                $execResponse = [];
                exec("composer show {$key} --direct --format=json", $execResponse);
                $json = json_decode($this->arr->of($execResponse)->join(' '));

                $this->libraries[] = [
                    $key,
                    "\033[0;33m{$json->versions[0]}\033[0m",
                    "\033[0;33m{$json->licenses[0]->osi}\033[0m",
                    "\033[0;31mtrue\033[0m",
                    $json->description
                ];
            }
        }

        return $this;
    }

    public function getLibraries(): array
    {
        return $this->libraries;
    }
}
