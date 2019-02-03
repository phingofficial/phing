<?php

/**
 * Tests the Echo Task
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.tasks.system
 */
class EchoTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/EchoTest.xml'
        );
    }

    public function testPropertyMsg()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a msg');
    }

    public function testPropertyMessage()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a message');
    }

    public function testInlineText()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('This is a nested inline text message');
    }

    public function testFileset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('EchoTest.xml');
    }

    public function testDirset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('ext');
        $this->assertInLogs('imports');
        $this->assertInLogs('system');
    }

    public function testFilesetInline()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('foo');
        $this->assertInLogs('EchoTest.xml');
    }

    public function testFilesetMsg()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs("foo\n");
        $this->assertInLogs('EchoTest.xml');
    }
}
