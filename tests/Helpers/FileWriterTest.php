<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test as Testing;
use ReflectionException;
use Tests\Providers\Helpers\FileWriterProviderTrait;

class FileWriterTest extends Test
{
    use FileWriterProviderTrait;

    private const string FILE_NAME = 'example.json';

    private FileWriter $fileWriter;
    private ClassFactory $classFactory;

    /**
     * @throws ReflectionException
     */
    protected function setUp(): void
    {
        $container = new Container();

        $this->fileWriter = $container->resolve(FileWriter::class);

        $this->classFactory = $container->resolve(ClassFactory::class);

        $this->initReflection($this->fileWriter);
    }

    /**
     * @throws ReflectionException
     */
    #[Testing]
    #[DataProvider('replaceContentProvider')]
    public function testReplaceContent(array $row, string $originalLine, string $return): void
    {
        $returnMethod = $this->getPrivateMethod('replaceContent', [
            'row' => $row,
            'originalLine' => $originalLine,
        ]);

        $this->assertSame($return, $returnMethod);
    }

    #[Testing]
    #[DataProvider('readFileRowsProvider')]
    public function readFileRows(array $rows, string $return): void
    {
        $this->classFactory
            ->create('example', ClassFactory::JSON_EXTENSION, './')
            ->add(
                <<<JSON
                {
                    "name": "Test",
                    "last_name-test": "Example"
                }
                JSON
            )
            ->close();

        $this->assertFileExists(self::FILE_NAME);

        $this->fileWriter->readFileRows(self::FILE_NAME, $rows);

        $this->assertJsonStringEqualsJsonFile(self::FILE_NAME, $return);

        unlink(self::FILE_NAME);

        $this->assertFileDoesNotExist(self::FILE_NAME);
    }

    #[Testing]
    #[DataProvider('readFileRowsWithMultipleRowsProvider')]
    public function readFileRowsWithMultipleRows(array $rows, string $return): void
    {
        $this->classFactory
            ->create('example', ClassFactory::JSON_EXTENSION, './')
            ->add(
                <<<JSON
                {
                    "name": "Test",
                    "last_name-test": "Example"
                }
                JSON
            )
            ->close();

        $this->assertFileExists(self::FILE_NAME);

        $this->fileWriter->readFileRows(self::FILE_NAME, $rows);

        $this->assertJsonStringEqualsJsonFile(self::FILE_NAME, $return);

        unlink(self::FILE_NAME);

        $this->assertFileDoesNotExist(self::FILE_NAME);
    }

    #[Testing]
    #[DataProvider('readFileRowsRemoveRowsProvider')]
    public function readFileRowsRemoveRows(array $rows, string $return): void
    {
        $this->classFactory
            ->create('example', ClassFactory::JSON_EXTENSION, './')
            ->add(
                <<<JSON
                {
                    "name": "Test",
                    "last_name-test": "Example"
                }
                JSON
            )
            ->close();

        $this->assertFileExists(self::FILE_NAME);

        $this->fileWriter->readFileRows(self::FILE_NAME, $rows);

        $this->assertJsonStringEqualsJsonFile(self::FILE_NAME, $return);

        unlink(self::FILE_NAME);

        $this->assertFileDoesNotExist(self::FILE_NAME);
    }
}
