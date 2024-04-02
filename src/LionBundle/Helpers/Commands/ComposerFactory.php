<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use Lion\Helpers\Arr;

/**
 * Gets the list of installed libraries and dev-libraries
 *
 * @property Arr $arr [Arr class object]
 * @property array $libraries [List of installed libraries and dev-libraries]
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class ComposerFactory
{
    /**
     * [Arr class object]
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * [List of installed libraries and dev-libraries]
     *
     * @var array $libraries
     */
    private array $libraries = [];

    /**
     * @required
     */
    public function setArr(Arr $arr): ComposerFactory
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * List of installed libraries
     *
     * @param object $composerJson [Composer json content]
     * @param array $extensions [Extensions that should be ignored]
     *
     * @return ComposerFactory
     */
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

    /**
     * List of installed libraries-dev
     *
     * @param object $composerJson [Composer json content]
     * @param array $extensions [Extensions that should be ignored]
     *
     * @return ComposerFactory
     */
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

    /**
     * Gets the libraries obtained
     *
     * @return array
     */
    public function getLibraries(): array
    {
        return $this->libraries;
    }
}
