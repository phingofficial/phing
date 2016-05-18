<?php
require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgCommitTask.php';
require_once dirname(__FILE__) . '/HgTestsHelper.php';

class HgCommitTaskTest extends BuildFileTest
{

    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4') < 0) {
            $this->markTestSkipped("Need PHP 5.4+ for this test");
        }
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgCommitTaskTest.xml'
        );
    }

    public function tearDown()
    {
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
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

