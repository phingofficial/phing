<?php

class HgRevertTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp(): void
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgRevertTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testFileNotSpecified()
    {
        $this->expectBuildExceptionContaining(
            'fileNotSpecified',
            "fileNotSpecified",
            "abort: no files or directories specified"
        );
        $this->assertInLogs('Executing: hg revert', Project::MSG_INFO);
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testRevertAll()
    {
        $this->executeTarget("revertAll");
        $this->assertInLogs('Executing: hg revert --all', Project::MSG_INFO);
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testRevertAllRevSet()
    {
        $this->expectBuildExceptionContaining(
            'revertAllWithRevisionSet',
            'revertAllWithRevisionSet',
            "abort: unknown revision 'deadbeef0a0b'!"
        );
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }
}
