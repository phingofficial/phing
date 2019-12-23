<?php

declare(strict_types=1);

/**
 * Tests the Relentless Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RelentlessTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/RelentlessTest.xml'
        );
    }

    /**
     * @return void
     */
    public function testRelentless(): void
    {
        $this->expectLogContaining(__FUNCTION__, 'Executing: task 3');
    }

    /**
     * @return void
     */
    public function testTerse(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('Executing: task 3');
    }

    /**
     * @return void
     */
    public function testFailure(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Relentless execution: 1 of 5 tasks failed.');

        $this->executeTarget(__FUNCTION__);

        $this->assertInLogs('Task task 3 failed: baz');
    }
}
