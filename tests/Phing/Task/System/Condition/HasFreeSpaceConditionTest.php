<?php

namespace Phing\Task\System\Condition;

use Phing\Test\Support\BuildFileTest;

class HasFreeSpaceConditionTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/HasFreeSpaceConditionTest.xml');
    }

    public function testPartitionNotSet()
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Please set the partition attribute.');
    }

    public function testNeededNotSet()
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Please set the needed attribute.');
    }

    public function testInvalidPartition()
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Error while retrieving free space.');
    }

    public function testEnoughSpace()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('HasFreeSpaceConditionTest: Enough space in disk.');
    }

    public function testNotEnoughSpace()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('HasFreeSpaceConditionTest: Not enough space in disk.');
    }
}
