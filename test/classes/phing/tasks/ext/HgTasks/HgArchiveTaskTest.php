<?php
require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgArchiveTask.php';
require_once __DIR__ . '/HgTestsHelper.php';

class HgArchiveTaskTest extends BuildFileTest
{
    public function setUp()
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgArchiveTaskTest.xml'
        );
    }

    public function tearDown()
    {
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testDestinationNotSpecified()
    {
        $this->expectBuildExceptionContaining(
            'destinationNotSpecified',
            "destinationNotSpecified",
            "Destination must be set."
        );
    }
}
