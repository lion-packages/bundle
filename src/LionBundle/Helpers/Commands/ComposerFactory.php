<?php

declare(strict_types=1);

namespace Lion\Bundle\Helpers\Commands;

use DI\Attribute\Inject;
use Lion\Helpers\Arr;
use stdClass;
use Symfony\Component\Console\Helper\TableSeparator;

/**
 * Gets the list of installed libraries and dev-libraries.
 *
 * @package Lion\Bundle\Helpers\Commands
 */
class ComposerFactory
{
    /**
     * Modify and build arrays with different indexes or values.
     *
     * @var Arr $arr
     */
    private Arr $arr;

    /**
     * List of installed libraries and dev-libraries.
     *
     * @var array<int, array<int, string>|TableSeparator> $libraries
     */
    private array $libraries = [];

    /**
     * Counter to validate the number of installed packages.
     *
     * @var int $count
     */
    private int $count = 0;

    #[Inject]
    public function setArr(Arr $arr): ComposerFactory
    {
        $this->arr = $arr;

        return $this;
    }

    /**
     * Gets the data of the installed library.
     *
     * @param string $library Name of the library.
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
     * Valid if the library has the required properties.
     *
     * @param stdClass $json Installed library data.
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
     * List of installed libraries.
     *
     * @param stdClass $composerJson Composer json content.
     *
     * @return ComposerFactory
     *
     * @codeCoverageIgnore
     */
    public function libraries(stdClass $composerJson): ComposerFactory
    {
        /**
         * @param bool $isDev Determines which libraries to obtain are required
         *                    or necessary for developers.
         *
         * @return void
         */
        $getLibraries = function (bool $isDev) use ($composerJson): void {
            /** @var array<string, string> $dependencies */
            $dependencies = $isDev ? (array) $composerJson->{'require-dev'} : (array) $composerJson->require;

            foreach ($dependencies as $library => $content) {
                if (preg_match('/\//', $library)) {
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

                    $type = $isDev ? 'true' : 'false';

                    $this->libraries[] = [
                        $library,
                        "\033[0;33m{$version}\033[0m",
                        "\033[0;33m{$license}\033[0m",
                        "\033[0;31m{$type}\033[0m",
                        wordwrap($description, 80, "\n", true),
                    ];

                    $this->libraries[] = new TableSeparator();

                    $this->count++;
                }
            }
        };

        $getLibraries(false);

        $getLibraries(true);

        return $this;
    }

    /**
     * Gets the libraries obtained.
     *
     * @return array<int, array<int, string>|TableSeparator>
     */
    public function getLibraries(): array
    {
        return $this->libraries;
    }

    /**
     * Returns the number of installed packages.
     *
     * @return int
     */
    public function getCount(): int
    {
        return $this->count;
    }
}
