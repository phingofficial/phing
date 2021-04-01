<?php

namespace Phing\Test\Task\System\Condition;

use Phing\Project;
use Phing\Test\Support\BuildFileTest;

/**
 * Tests PDOSQLExecTask as condition
 *
 * @author  Jawira Portugal <dev@tugal.be>
 */
class PDOSQLExecTaskConditionTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/PDOSQLExecTaskConditionTest.xml'
        );
    }

    public function testUrlIsRequiredException()
    {
        $this->expectSpecificBuildException(
            __FUNCTION__,
            'url property not set in database condition',
            'url is required'
        );
    }

    public function testFalseWhenInvalidHost()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Trying to reach mysql:host=dummy', Project::MSG_DEBUG);
        $this->assertInLogs('SQLSTATE[HY000] [2002]', Project::MSG_VERBOSE);
        $this->assertInLogs('pdosqlexec condition returned false', Project::MSG_INFO);
    }

    public function testFalseWhenInvalidDriver()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Trying to reach invalid:host=localhost', Project::MSG_DEBUG);
        $this->assertInLogs('could not find driver', Project::MSG_VERBOSE);
        $this->assertInLogs('pdosqlexec condition returned false', Project::MSG_INFO);
    }

    public function testCompatibleWithConditionTask()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('condition.result', 'condition-not-met');
        $this->assertInLogs('Trying to reach mysql:host=localhost', Project::MSG_DEBUG);
    }

    public function testCompatibleWithWaitForTask()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('waitfor.timeout', 'true');
        $this->assertInLogs('Trying to reach mysql:host=localhost', Project::MSG_DEBUG);
    }

    public function testSuccessfulCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('pdosqlexec condition returned true', Project::MSG_INFO);
    }
}
