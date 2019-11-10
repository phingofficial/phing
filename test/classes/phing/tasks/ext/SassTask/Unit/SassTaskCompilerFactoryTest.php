<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SassTaskCompilerFactoryTest extends TestCase
{

    public function testItFailsWhenNoCompilerIsSet(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('false');
        $sassTask->setUseScssphp('false');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Neither sass nor scssphp are to be used.');

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

        $this->assertInstanceOf(SassCompiler::class, $compiler);
    }

    public function testItPrefersSassCompiler(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('true');
        $fileSystem = new FileSystemWhichStub(true);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $compiler = $factory->prepareCompiler($sassTask);

        $this->assertInstanceOf(SassCompiler::class, $compiler);
    }

    public function testItFailsWhenSassExecutableNotFound(): void
    {
        $sassTask = new SassTask();
        $sassTask->setUseSass('true');
        $sassTask->setUseScssphp('false');
        $sassTask->setExecutable('sass');
        $fileSystem = new FileSystemWhichStub(false);
        $factory = new SassTaskCompilerFactory($fileSystem);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('sass not found. Install sass.');

        $factory->prepareCompiler($sassTask);
    }
}
