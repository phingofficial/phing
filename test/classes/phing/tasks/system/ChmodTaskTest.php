<?php

/**
 * Tests the Chmod Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class ChmodTaskTest extends BuildFileTest
{
    public function setUp(): void    {
        if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
            $this->markTestSkipped("chmod tests don't work on Windows");
            return;
        }

        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ChmodTaskTest.xml'
        );
    }

    public function tearDown(): void    {
        $this->executeTarget('clean');
    }

    public function testChangeModeFile()
    {
        $this->executeTarget(__FUNCTION__);

        clearstatcache();
        $mode = fileperms(PHING_TEST_BASE . '/etc/tasks/system/tmp/chmodtest');

        $this->assertEquals(octdec('0700'), $mode & 0777, 'chmodtest mode should have changed to 0400');
    }

    public function testChangeModeFileSet()
    {
        $this->executeTarget(__FUNCTION__);

        clearstatcache();
        $mode = fileperms(PHING_TEST_BASE . '/etc/tasks/system/tmp/chmodtest');

        $this->assertEquals(octdec('0700'), $mode & 0777, 'chmodtest mode should have changed to 0400');
    }

    public function testChangeModeDirSet()
    {
        $this->executeTarget(__FUNCTION__);

        clearstatcache();
        $mode = fileperms(PHING_TEST_BASE . '/etc/tasks/system/tmp/A');

        $this->assertEquals(octdec('0700'), $mode & 0777, 'chmodtest mode should have changed to 0400');
    }
}
