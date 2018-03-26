<?php

class SassTaskTest extends BuildFileTest
{

    private const SASS_TEST_BASE = PHING_TEST_BASE . "/etc/tasks/ext/sass/";

    /** @var FileSystem */
    private $fs;

    /** @var SassTask */
    private $object;

    /** @var SassTaskAssert */
    private $sassTaskAssert;

    public function setUp(): void
    {
        $this->configureProject(self::SASS_TEST_BASE . "SassTaskTest.xml");
        $this->object = new SassTask();
        $this->sassTaskAssert = new SassTaskAssert();
        $this->fs = FileSystem::getFileSystem();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        if (file_exists(self::SASS_TEST_BASE . "test.css")) {
            $this->fs->unlink(self::SASS_TEST_BASE . "test.css");
        }
        if (file_exists(self::SASS_TEST_BASE . "test.css.map")) {
            $this->fs->unlink(self::SASS_TEST_BASE . "test.css.map");
        }
        if (is_dir(self::SASS_TEST_BASE . ".sass-cache")) {
            $this->fs->rmdir(self::SASS_TEST_BASE . ".sass-cache", true);
        }
    }

    public function testCheckDefaults(): void
    {
        $this->sassTaskAssert->assertDefaults($this->object);
    }

    public function testSetStyleCompactViaSetStyle(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compact');
        $this->sassTaskAssert->assertCompactStyle($this->object);
    }

    public function testSetStyleCompactViaOwnMethod(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setCompact('yes');
        $this->sassTaskAssert->assertCompactStyle($this->object);
    }

    public function testSetStyleCompressedViaSetStyle(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compressed');
        $this->sassTaskAssert->assertCompressedStyle($this->object);
    }

    public function testSetStyleCompressedViaOwnMethod(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setCompressed('yes');
        $this->sassTaskAssert->assertCompressedStyle($this->object);
    }

    public function testNothing(): void
    {
        $this->executeTarget("nothing");
        $this->assertInLogs(
            "Neither sass nor scssphp are to be used.",
            Project::MSG_ERR
        );
    }

    public function testSetStyleToUnrecognised(): void
    {
        $this->executeTarget("testSettingUnrecognisedStyle");
        $this->assertInLogs('Style compacted ignored', Project::MSG_INFO);
        $this->assertInLogs(
            "Neither sass nor scssphp are to be used.",
            Project::MSG_ERR
        );
    }

    public function testNoFilesetAndNoFileSet(): void
    {
        $this->expectBuildExceptionContaining(
            'testNoFilesetAndNoFileSet',
            'testNoFilesetAndNoFileSet',
            "Missing either a nested fileset or attribute 'file'"
        );
    }

    public function testItCompilesWithSass(): void
    {
        if (!$this->fs->which('sass')) {
            $this->markTestSkipped('Sass not found');
        }
        $this->executeTarget("testItCompilesWithSass");
        $this->assertTrue(file_exists(self::SASS_TEST_BASE . "test.css"));
    }

    public function testItCompilesWithScssPhp(): void
    {
        if (!class_exists('\Leafo\ScssPhp\Compiler')) {
            $this->markTestSkipped('ScssPhp not found');
        }
        $this->executeTarget("testItCompilesWithScssPhp");
        $this->assertTrue(file_exists(self::SASS_TEST_BASE . "test.css"));
    }
}
