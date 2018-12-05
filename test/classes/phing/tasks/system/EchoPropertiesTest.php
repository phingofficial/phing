<?php

/**
 * Tests the EchoProperties Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class EchoPropertiesTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/EchoPropertiesTest.xml'
        );
    }

    public function tearDown()
    {
        $this->executeTarget('cleanup');
    }

    public function testEchoProperties()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists($this->getProject()->getProperty('property.file'));
    }
}
