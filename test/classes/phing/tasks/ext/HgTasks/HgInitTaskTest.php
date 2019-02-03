<?php

class HgInitTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp(): void
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgInitTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testHgInit()
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        $repository = PHING_TEST_BASE . '/tmp/hgtest';
        $HGdir = $repository . '/.hg';
        $this->executeTarget('hgInit');
        $this->assertInLogs('Initializing');
        $this->assertDirectoryExists($repository);
        $this->assertDirectoryExists($HGdir);
    }

    public function testWrongRepository()
    {
        $this->expectBuildExceptionContaining('wrongRepository', 'is not a directory', "is not a directory");
    }
}
