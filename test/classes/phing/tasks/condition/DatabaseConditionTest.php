<?php

/**
 * Tests Database Condition
 *
 * @author  Jawira Portugal <dev@tugal.be>
 * @package phing.tasks.system
 */
class DatabaseConditionTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/DatabaseConditionTest.xml'
        );
    }

    public function testDsnIsRequiredException()
    {
        $this->expectSpecificBuildException(__FUNCTION__,
                                            'dsn property not set in database condition',
                                            'dsn is required');
    }

    public function testFalseWhenInvalidHost()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Trying to reach: mysql:host=dummy', Project::MSG_DEBUG);
        $this->assertInLogs('Name or service not known', Project::MSG_VERBOSE);
        $this->assertInLogs('Database condition returned false', Project::MSG_INFO);
    }

    public function testFalseWhenInvalidDriver()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Trying to reach: invalid:host=localhost', Project::MSG_DEBUG);
        $this->assertInLogs('could not find driver', Project::MSG_VERBOSE);
        $this->assertInLogs('Database condition returned false', Project::MSG_INFO);
    }

    public function testCompatibleWithConditionTask()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('condition.result', 'condition-not-met');
        $this->assertInLogs('Trying to reach: mysql:host=localhost', Project::MSG_DEBUG);
    }

    public function testCompatibleWithWaitForTask()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('waitfor.timeout', 'true');
        $this->assertInLogs('Trying to reach: mysql:host=localhost', Project::MSG_DEBUG);
    }

    public function testSuccessfulCondition()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Database condition returned true', Project::MSG_INFO);
    }

}
