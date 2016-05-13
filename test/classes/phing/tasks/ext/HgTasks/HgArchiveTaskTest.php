<?php
require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgArchiveTask.php';
require_once dirname(__FILE__) . '/HgTestsHelper.php';

class HgArchiveTaskTest extends BuildFileTest
{

    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4') < 0) {
            $this->markTestSkipped("Need PHP 5.4+ for this test");
        }
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
