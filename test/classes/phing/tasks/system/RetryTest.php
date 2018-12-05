<?php

/**
 * Tests the Retry Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RetryTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/RetryTest.xml'
        );
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Task [fail] failed after [3] attempts; giving up
     */
    public function testRetry()
    {
        $this->expectLogContaining(__FUNCTION__, 'This task failed.');
    }
}
