<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SassTaskCompilerFactoryTest extends TestCase
{

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Neither sass nor scssphp are to be used.
     */
    public function testItFailsWhenNoCompilerIsSet(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('false');
        $sassTask->setUseScssphp('false');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $factory->prepareCompiler($sassTask);
    }

    public function testItReturnSassCompiler(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('false');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $compiler = $factory->prepareCompiler($sassTask);

        self::assertInstanceOf(SassCompiler::class, $compiler);
    }

    public function testItPrefersSassCompiler(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('true');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $compiler = $factory->prepareCompiler($sassTask);

        self::assertInstanceOf(SassCompiler::class, $compiler);
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage sass not found. Install sass.
     */
    public function testItFailsWhenSassExecutableNotFound(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('false');
        $sassTask->setExecutable('sass');
        $fileSystem = new FileSystemWhichStub(false);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $factory->prepareCompiler($sassTask);
    }
}
