<?php

/**
 * Tests the ElseIf Task
 *
 * @author  Paul Edenburg <pauledenburg@gmail.com>
 * @package phing.tasks.system
 */
class ElseIfTaskTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/ElseIfTest.xml'
        );
    }

    /**
     * Test the 'elseif' conditional of the if-task
     *
     * @test
     */
    public function testAddThen()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("Elseif: The value of property foo is 'foo'");
    }

    /**
     * Test that evaluating a correct elseif condition gives the
     * expected result
     *
     * @test
     */
    public function testEvaluate()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("Elseif: The value of property foo is foo");
    }

    /**
     * test that a BuildException is thrown when we've got two
     * conditions inside an elseif-task
     *
     * @test
     */
    public function testMultipleConditions()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 condition in your elseif-statement';
        $msg = 'You must not nest more than one condition into <elseif>';

        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }

    /**
     * test that a BuildException is thrown when we've got
     * no conditions inside an elseif-task
     *
     * @test
     */
    public function testNoConditions()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you need to have a condition inside the <elseif>';
        $msg = 'You must nest a condition into <elseif>';

        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }
}
