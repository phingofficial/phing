<?php

class HgCommitTaskTest extends BuildFileTest
{
    public function setUp()
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgCommitTaskTest.xml'
        );
    }

    public function tearDown()
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testMessageNotSpecified()
    {
        $this->expectBuildExceptionContaining(
            'messageNotSpecified',
            "message is not specified",
            '"message" is a required parameter'
        );
    }

    public function testUserNotEmptyString()
    {
        $this->expectBuildExceptionContaining(
            'userNotEmptyString',
            'user parameter can not be an empty string',
            '"user" parameter can not be set to ""'
        );
    }
}
