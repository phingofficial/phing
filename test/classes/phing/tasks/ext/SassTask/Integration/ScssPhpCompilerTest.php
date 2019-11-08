<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ScssPhpCompilerTest extends TestCase
{

    private const SASS_TEST_BASE = PHING_TEST_BASE . "/etc/tasks/ext/sass/";

    /** @var ScssPhpCompiler */
    private $compiler;

    public function setUp(): void
    {
        if (!class_exists('\ScssPhp\ScssPhp\Compiler')) {
            self::markTestSkipped('ScssPhp not found');
        }

        $this->compiler = new ScssPhpCompiler('compressed', 'UTF-8', false, '');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists(self::SASS_TEST_BASE . 'test.css')) {
            $fs = FileSystem::getFileSystem();
            $fs->unlink(self::SASS_TEST_BASE . 'test.css');
        }
    }

    public function testItProducesAnyCompiledOutput(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'test.scss',
            self::SASS_TEST_BASE . 'test.css',
            false
        );

        self::assertFileExists(self::SASS_TEST_BASE . 'test.css');
    }

    public function testItNotProducesAnyCompiledOutputWhenNoInput(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'non-existing.sass',
            self::SASS_TEST_BASE . 'test.css',
            false
        );

        self::assertFileNotExists(self::SASS_TEST_BASE . 'test.css');
    }

    /**
     * @expectedException BuildException
     */
    public function testItThrowsExceptionWhenFailOnErrorIsSet(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'non-existing.sass',
            self::SASS_TEST_BASE . 'test.css',
            true
        );

        self::assertFileNotExists(self::SASS_TEST_BASE . 'test.css');
    }
}
