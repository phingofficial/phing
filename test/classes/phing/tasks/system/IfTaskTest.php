<?php

/**
 * Tests the If Task
 *
 * @author  Paul Edenburg <pauledenburg@gmail.com>
 * @package phing.tasks.system
 */
class IfTaskTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/IfTest.xml'
        );
    }

    /**
     * Test the 'elseif' conditional of the if-task
     *
     * @test
     */
    public function testAddElseIf()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("The value of property foo is 'foo'");
    }

    /**
     * Test the 'then' conditional of the if-task
     *
     * @test
     */
    public function testAddThen()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("The value of property foo is 'foo'");
    }

    /**
     * Test the 'else' conditional of the if-task
     *
     * @test
     */
    public function testAddElse()
    {
        // execute the PHING target with the same name as this function
        $this->executeTarget(__FUNCTION__);

        // check the output for the expected value
        $this->assertInLogs("The value of property foo is not 'bar'");
    }

    /**
     * test that a buildexception is thrown when we've got two
     * <then> statements in an if-task
     *
     * @test
     */
    public function testAddDoubleThen()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 <then> directive in your if-statement';
        $msg = 'You must not nest more than one <then> into <if>';
        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }

    /**
     * test that a BuildException is thrown when we've got two
     * <else> statements in an if-task
     *
     * @test
     */
    public function testAddDoubleElse()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 <else> directive in your if-statement';
        $msg = 'You must not nest more than one <else> into <if>';
        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }

    /**
     * test that a BuildException is thrown when we've got two
     * <else> statements in an if-task
     *
     * @test
     */
    public function testMultipleConditions()
    {
        // execute the phing target and expect it to throw a buildexception
        $target = __FUNCTION__;
        $cause = 'you cannot have more than 1 condition in your if-statement';
        $msg = 'You must not nest more than one condition into <if>';
        $this->expectBuildExceptionContaining($target, $cause, $msg);
    }
}
