<?php

/**
 * Tests the XorCondition
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.condition
 */
class XorConditionTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/XorConditionTest.xml'
        );
    }

    public function testEmpty()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('isEmpty');
    }

    public function test1()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('testTrue');
    }

    public function test0()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('testFalse');
    }

    public function test10()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test10');
    }

    public function test01()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test01');
    }

    public function test00()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test00');
    }

    public function test11()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('test11');
    }
}
