<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use DI\Attribute\Inject;
use Lion\Helpers\Arr;
use stdClass;

/**
 * Gets the list of installed libraries and dev-libraries
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
     * @var array<int, array<int, string>> $libraries
     */
    private array $libraries = [];

    #[Inject]
    public function setArr(Arr $arr): ComposerFactory
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * Gets the data of the installed library
     *
     * @param string $library [Name of the library]
     *
     * @return stdClass
     */
    private function getLibrariesWithCommand(string $library): stdClass
    {
        exec("composer show {$library} --direct --format=json", $exec);

        /** @var stdClass $json */
        $json = json_decode(
            $this->arr
                ->of($exec)
                ->join(' ')
        );

        return $json;
    }

    /**
     * Valid if the library has the required properties
     *
     * @param stdClass $json [Installed library data]
     *
     * @return bool
     */
    private function validateLibrary(stdClass $json): bool
    {
        if (!isset($json->description, $json->versions, $json->licenses)) {
            return false;
        }

        if (!is_array($json->versions) || !is_array($json->licenses)) {
            return false;
        }

        if (!isset($json->versions[0], $json->licenses[0])) {
            return false;
        }

        return true;
    }

    /**
     * List of installed libraries
     *
     * @param stdClass $composerJson [Composer json content]
     * @param array<int, string> $extensions [Extensions that should be ignored]
     *
     * @return ComposerFactory
     *
     * @codeCoverageIgnore
     */
    public function libraries(stdClass $composerJson, array $extensions): ComposerFactory
    {
        /** @var array<string, string> $dependencies */
        $dependencies = (array) $composerJson->require;

        foreach ($dependencies as $library => $content) {
            if (!in_array($library, $extensions, true)) {
                $json = $this->getLibrariesWithCommand($library);

                if (!$this->validateLibrary($json)) {
                    continue;
                }

                /** @var string $description */
                $description = $json->description;

                /**
                 * @var string $version
                 *
                 * @phpstan-ignore-next-line
                 */
                $version = $json->versions[0];

                /**
                 * @var stdClass $licenses
                 *
                 * @phpstan-ignore-next-line
                 */
                $licenses = $json->licenses[0];

                /** @var string $license */
                $license = $licenses->osi;

                $type = 'false';

                $this->libraries[] = [
                    $library,
                    "\033[0;33m{$version}\033[0m",
                    "\033[0;33m{$license}\033[0m",
                    "\033[0;31m{$type}\033[0m",
                    $description,
                ];
            }
        }

        return $this;
    }

    /**
     * List of installed libraries-dev
     *
     * @param stdClass $composerJson [Composer json content]
     * @param array<int, string> $extensions [Extensions that should be ignored]
     *
     * @return ComposerFactory
     *
     * @codeCoverageIgnore
     */
    public function librariesDev(stdClass $composerJson, array $extensions): ComposerFactory
    {
        /** @var array<string, string> $dependencies */
        $dependencies = (array) $composerJson->{'require-dev'};

        foreach ($dependencies as $library => $content) {
            if (!in_array($library, $extensions, true)) {
                $json = $this->getLibrariesWithCommand($library);

                if (!$this->validateLibrary($json)) {
                    continue;
                }

                /** @var string $description */
                $description = $json->description;

                /**
                 * @var string $version
                 *
                 * @phpstan-ignore-next-line
                 */
                $version = $json->versions[0];

                /**
                 * @var stdClass $licenses
                 *
                 * @phpstan-ignore-next-line
                 */
                $licenses = $json->licenses[0];

                /** @var string $license */
                $license = $licenses->osi;

                $type = 'true';

                $this->libraries[] = [
                    $library,
                    "\033[0;33m{$version}\033[0m",
                    "\033[0;33m{$license}\033[0m",
                    "\033[0;31m{$type}\033[0m",
                    $description,
                ];
            }
        }

        return $this;
    }

    /**
     * Gets the libraries obtained
     *
     * @return array<int, array<int, string>>
     */
    public function getLibraries(): array
    {
        return $this->libraries;
    }
}
