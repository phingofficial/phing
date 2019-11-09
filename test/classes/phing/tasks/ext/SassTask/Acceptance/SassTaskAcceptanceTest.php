<?php

class SassTaskAcceptanceTest extends BuildFileTest
{
    use SassCleaner;

    private const SASS_TEST_BASE = PHING_TEST_BASE . "/etc/tasks/ext/sass/";

    /** @var FileSystem */
    private $fs;

    public function setUp(): void
    {
        $this->configureProject(self::SASS_TEST_BASE . "SassTaskTest.xml");
        $this->fs = FileSystem::getFileSystem();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->sassCleanUp(self::SASS_TEST_BASE, 'test.css');
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Neither sass nor scssphp are to be used.
     */
    public function testNothing(): void
    {
        $this->executeTarget("nothing");
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Neither sass nor scssphp are to be used.
     */
    public function testSetStyleToUnrecognised(): void
    {
        $this->executeTarget("testSettingUnrecognisedStyle");
        $this->assertInLogs('Style compacted ignored', Project::MSG_INFO);
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
        $this->assertFileExists(self::SASS_TEST_BASE . "test.css");
    }

    public function testItCompilesWithScssPhp(): void
    {
        if (!class_exists('\ScssPhp\ScssPhp\Compiler')) {
            $this->markTestSkipped('ScssPhp not found');
        }
        $this->executeTarget("testItCompilesWithScssPhp");
        $this->assertFileExists(self::SASS_TEST_BASE . "test.css");
    }
}
