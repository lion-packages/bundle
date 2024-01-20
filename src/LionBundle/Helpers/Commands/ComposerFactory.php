<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Helpers\Arr;

class ComposerFactory
{
    private Arr $arr;
    private object $composerJson;
    private array $libraries = [];

    public function __construct(object $composerJson, array $extensions)
    {
        $this->arr = new Arr();
        $this->composerJson = $composerJson;

        $this->libraries($extensions);
        $this->librariesDev($extensions);
    }

    private function libraries(array $extensions): void
    {
        foreach ($this->composerJson->require as $key => $library) {
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
    }

    private function librariesDev(array $extensions): void
    {
        foreach ($this->composerJson->{'require-dev'} as $key => $library) {
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
    }

    public function getLibraries(): array
    {
        return $this->libraries;
    }
}
