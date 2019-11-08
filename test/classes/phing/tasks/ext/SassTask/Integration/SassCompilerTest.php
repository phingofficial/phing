<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class SassCompilerTest extends TestCase
{

    use SassCleaner;

    private const SASS_TEST_BASE = PHING_TEST_BASE . "/etc/tasks/ext/sass/";

    /** @var SassCompiler */
    private $compiler;

    protected function setUp(): void
    {
        $fs = FileSystem::getFileSystem();
        if (!$fs->which('sass')) {
            self::markTestSkipped('Sass not found');
        }

        $this->compiler = new SassCompiler('sass', '');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->sassCleanUp(self::SASS_TEST_BASE, 'test.css');
    }

    public function testItProducesAnyCompiledOutput(): void
    {
        $this->compiler->compile(
            self::SASS_TEST_BASE . 'test.sass',
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
