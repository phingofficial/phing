<?php

class HgPushTaskTest extends BuildFileTest
{
    public function setUp()
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgPushTaskTest.xml'
        );
    }

    public function tearDown()
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testWrongRepositoryDirDoesntExist()
    {
        $this->expectBuildExceptionContaining(
            'wrongRepositoryDirDoesntExist',
            'repository directory does not exist',
            "Repository directory 'inconcievable-buttercup' does not exist."
        );
    }

    public function testWrongRepository()
    {
        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'wrong repository',
            "abort"
        );
    }
}
