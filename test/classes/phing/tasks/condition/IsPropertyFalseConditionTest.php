<?php

/**
 * Tests the IsPropertyFalse Condition
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class IsPropertyFalseConditionTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/IsPropertyTrueFalseTest.xml'
        );
    }

    public function testIsPropertyFalse()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('IsFalse');
    }

    public function testIsPropertyNotFalse()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('IsNotFalse');
    }
}
