<?php
require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgAddTask.php';
require_once dirname(__FILE__) . '/HgTestsHelper.php';

class HgAddTaskTest extends BuildFileTest
{

    public function setUp()
    {
        if (version_compare(PHP_VERSION, '5.4') < 0) {
            $this->markTestSkipped("Need PHP 5.4+ for this test");
        }
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgAddTaskTest.xml'
        );
    }

    public function tearDown()
    {
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
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
