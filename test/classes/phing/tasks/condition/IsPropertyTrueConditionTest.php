<?php

/**
 * Tests the IsPropertyTrue/-False Tasks
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class IsPropertyTrueConditionTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/IsPropertyTrueFalseTest.xml'
        );
    }

    public function testIsPropertyTrue()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('IsTrue');
    }

    public function testIsPropertyNotTrue()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('IsNotTrue');
    }
}
