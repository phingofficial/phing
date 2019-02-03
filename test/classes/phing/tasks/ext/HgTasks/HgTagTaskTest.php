<?php

class HgTagTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp(): void
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgTagTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testRepoDoesntExist()
    {
        $this->expectBuildExceptionContaining(
            'wrongRepositoryDirDoesntExist',
            'wrongRepositoryDirDoesntExist',
            "Repository directory 'inconcievable-buttercup' does not exist."
        );
    }

    /*
    public function testTag()
    {
        $this->expectBuildExceptionContaining(
            "tag",
            "tag",
            "abort: cannot tag null revision"
        );
        $this->assertInLogs('Executing: tag --user \'test\' new-tag');
    }
    */

    public function testRevision()
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        $this->expectBuildExceptionContaining(
            "testRevision",
            "testRevision",
            "abort: unknown revision 'deadbeef'"
        );
        $this->assertInLogs(
            'Executing: tag --rev \'deadbeef\' --user \'test\' new-tag'
        );
    }
}
