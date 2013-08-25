<?php

require_once 'phing/BuildFileTest.php';

/**
 * Tests the Condition Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @version $Id$
 * @package phing.tasks.system
 */
class ConditionTaskTest extends BuildFileTest
{

    public function setUp()
    {
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

    /*
    Temporarily disabled due to http://www.phing.info/trac/ticket/1041
    
    public function testCustomCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('isCustom');
    }*/
}

