<?php

class NotifySendTaskTest extends BuildFileTest
{
    protected $object;

    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/NotifySendTaskTest.xml");
        $this->object = new NotifySendTask();
    }

    public function testEmptyMessage()
    {
        $this->executeTarget("testEmptyMessage");
        $this->assertInLogs('cmd: notify-send -i info Phing');
        $this->assertInLogs("Message: ''", Project::MSG_DEBUG);
        // Assert/ensure the silent attribute has been set.
        $this->assertInLogs('Silent flag set; not executing', Project::MSG_DEBUG);
    }

    public function testSettingTitle()
    {
        $this->object->setTitle("Test");
        self::assertEquals("Test", $this->object->getTitle());
        $this->object->setTitle("Test Again");
        self::assertEquals("Test Again", $this->object->getTitle());
    }

    public function testSettingMsg()
    {
        $this->object->setMsg("Test");
        self::assertEquals("Test", $this->object->getMsg());
        $this->object->setMsg("Test Again");
        self::assertEquals("Test Again", $this->object->getMsg());
    }

    public function testSetStandardIcon()
    {
        $this->object->setIcon("info");
        self::assertEquals("info", $this->object->getIcon());

        $this->object->setIcon("error");
        self::assertEquals("error", $this->object->getIcon());

        $this->object->setIcon("warning");
        self::assertEquals("warning", $this->object->getIcon());
    }

    public function testSetNonStandardIcon()
    {
        $this->object->setIcon("informational");
        self::assertEquals("info", $this->object->getIcon());
    }
}
