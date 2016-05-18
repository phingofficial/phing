<?php
require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgTagTask.php';
require_once dirname(__FILE__) . '/HgTestsHelper.php';

class HgTagTaskTest extends BuildFileTest
{

    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4') < 0) {
            $this->markTestSkipped("Need PHP 5.4+ for this test");
        }
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgTagTaskTest.xml'
        );
    }

    public function tearDown()
    {
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
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

