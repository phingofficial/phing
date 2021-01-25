<?php

namespace Phing\Tasks\System;

use Phing\Exception\BuildException;
use Phing\Support\BuildFileTest;

/**
 * Tests the Relentless Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class RelentlessTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/RelentlessTest.xml'
        );
    }

    public function testRelentless()
    {
        $this->expectLogContaining(__FUNCTION__, 'Executing: task 3');
    }

    public function testTerse()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertNotInLogs('Executing: task 3');
    }

    public function testFailure()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Relentless execution: 1 of 5 tasks failed.');

        $this->executeTarget(__FUNCTION__);

        $this->assertInLogs('Task task 3 failed: baz');
    }
}
