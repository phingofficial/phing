<?php

declare(strict_types=1);

/**
 * Tests PDOSQLExecTask as condition
 *
 * @author  Jawira Portugal <dev@tugal.be>
 * @package phing.tasks.system
 */
class PDOSQLExecTaskConditionTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/PDOSQLExecTaskConditionTest.xml'
        );
    }

    /**
     * @return void
     */
    public function testUrlIsRequiredException(): void
    {
        $this->expectSpecificBuildException(
            __FUNCTION__,
            'url property not set in database condition',
            'url is required'
        );
    }

    /**
     * @return void
     */
    public function testFalseWhenInvalidHost(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Trying to reach mysql:host=dummy', Project::MSG_DEBUG);
        $this->assertInLogs('SQLSTATE[HY000] [2002]', Project::MSG_VERBOSE);
        $this->assertInLogs('pdosqlexec condition returned false', Project::MSG_INFO);
    }

    /**
     * @return void
     */
    public function testFalseWhenInvalidDriver(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Trying to reach invalid:host=localhost', Project::MSG_DEBUG);
        $this->assertInLogs('could not find driver', Project::MSG_VERBOSE);
        $this->assertInLogs('pdosqlexec condition returned false', Project::MSG_INFO);
    }

    /**
     * @return void
     */
    public function testCompatibleWithConditionTask(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('condition.result', 'condition-not-met');
        $this->assertInLogs('Trying to reach mysql:host=localhost', Project::MSG_DEBUG);
    }

    /**
     * @return void
     */
    public function testCompatibleWithWaitForTask(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('waitfor.timeout', 'true');
        $this->assertInLogs('Trying to reach mysql:host=localhost', Project::MSG_DEBUG);
    }

    /**
     * @return void
     */
    public function testSuccessfulCondition(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('pdosqlexec condition returned true', Project::MSG_INFO);
    }
}
