<?php

require_once 'phing/BuildFileTest.php';
require_once '../classes/phing/tasks/ext/hg/HgInitTask.php';
require_once __DIR__ . '/HgTestsHelper.php';

class HgInitTaskTest extends BuildFileTest
{
    public function setUp()
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgInitTaskTest.xml'
        );
    }

    public function tearDown()
    {
        HgTestsHelper::rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }
    public function testHgInit()
    {
        $repository = PHING_TEST_BASE . '/tmp/hgtest';
        $HGdir = $repository . '/.hg';
        $this->executeTarget('hgInit');
        $this->assertInLogs('Initializing');
        $this->assertTrue(is_dir($repository));
        $this->assertTrue(is_dir($HGdir));
    }

    public function testWrongRepository()
    {
        $this->expectBuildExceptionContaining('wrongRepository', 'is not a directory', "is not a directory");
    }
}
