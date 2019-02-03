<?php

/**
 * Tests the Recorder Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RecorderTaskTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/RecorderTaskTest.xml'
        );
    }

    public function tearDown(): void    {
        $this->executeTarget('cleanup');
    }

    public function testRecordtoFiles()
    {
        $this->executeTarget(__FUNCTION__);
        $fileContent = $this->getProject()->getProperty('file.content');
        $fileRows = explode(PHP_EOL, $fileContent);
        $this->assertEquals($fileRows[0], '     [echo] recorder test');
    }
}
