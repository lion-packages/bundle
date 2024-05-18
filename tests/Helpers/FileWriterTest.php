<?php

declare(strict_types=1);

namespace Tests\Helpers;

use Lion\Bundle\Helpers\Commands\ClassFactory;
use Lion\Bundle\Helpers\FileWriter;
use Lion\Dependency\Injection\Container;
use Lion\Test\Test;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Providers\Helpers\FileWriterProviderTrait;

class FileWriterTest extends Test
{
    use FileWriterProviderTrait;

    const FILE_NAME = 'example.json';

    private FileWriter $fileWriter;
    private ClassFactory $classFactory;

    protected function setUp(): void
    {
        $container = new Container();

        $this->fileWriter = $container->injectDependencies(new FileWriter());

        $this->classFactory = $container->injectDependencies(new ClassFactory());

        $this->initReflection($this->fileWriter);
    }

    #[DataProvider('replaceContentProvider')]
    public function testReplaceContent(array $row, string $modifiedLine, string $originalLine): void
    {
        $returnMethod = $this->getPrivateMethod('replaceContent', [$row, $modifiedLine, $originalLine]);

        $this->assertEquals(strlen($modifiedLine), strlen($returnMethod));
        $this->assertEquals($modifiedLine, $returnMethod);
    }

    #[DataProvider('readFileRowsProvider')]
    public function testReadFileRows(array $rows, string $return): void
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

    #[DataProvider('readFileRowsWithMultipleRowsProvider')]
    public function testReadFileRowsWithMultipleRows(array $rows, string $return): void
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

    #[DataProvider('readFileRowsRemoveRowsProvider')]
    public function testReadFileRowsRemoveRows(array $rows, string $return): void
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
