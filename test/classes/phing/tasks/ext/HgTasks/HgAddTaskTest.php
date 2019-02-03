<?php

class HgAddTaskTest extends BuildFileTest
{
    public function setUp(): void    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgAddTaskTest.xml'
        );
    }

    public function tearDown(): void    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testWrongRepository()
    {
        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'is not a directory',
            "does not exist"
        );
    }
}
