<?php

/**
 * Tests the Condition Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class ConditionTaskTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ConditionTest.xml'
        );
    }

    public function testEquals()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isEquals');
    }

    public function testContains()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isContains');
    }

    public function testCustomCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isCustom');
    }

    public function testReferenceExists()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('ref.exists');
    }

    public function testSocketCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('socket');
    }

    public function testMatches()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('matches', 'true');
    }
}
