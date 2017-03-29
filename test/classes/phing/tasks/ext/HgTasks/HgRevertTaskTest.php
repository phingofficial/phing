<?php
require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgRevertTask.php';
require_once __DIR__ . '/HgTestsHelper.php';

class HgRevertTaskTest extends BuildFileTest
{
    public function setUp()
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgRevertTaskTest.xml'
        );
    }

    public function tearDown()
    {
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testFileNotSpecified()
    {
        $this->expectBuildExceptionContaining(
            'fileNotSpecified',
            "fileNotSpecified",
            "abort: no files or directories specified"
        );
        $this->assertInLogs('Executing: hg revert', Project::MSG_INFO);
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testRevertAll()
    {
        $this->executeTarget("revertAll");
        $this->assertInLogs('Executing: hg revert --all', Project::MSG_INFO);
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testRevertAllRevSet()
    {
        $this->expectBuildExceptionContaining(
            'revertAllWithRevisionSet',
            'revertAllWithRevisionSet',
            "abort: unknown revision 'deadbeef0a0b'!"
        );
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }
}
