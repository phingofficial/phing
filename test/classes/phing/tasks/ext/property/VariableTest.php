<?php

require_once 'phing/BuildFileTest.php';

/**
 * Tests the PropertyRegexTask Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class VariableTest extends BuildFileTest
{

    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/property/VariableTest.xml'
        );
    }

    public function testVariable()
    {
        $this->executeTarget(__FUNCTION__);

        $this->assertInLogs('1: aazz');
        $this->assertInLogs('2: aazz');
        $this->assertInLogs('3: x = 6');
        $this->assertInLogs('4: x = 12');
        $this->assertInLogs('5: x = 6 + 12');
        $this->assertInLogs('6: I  am  a  string.');
        $this->assertInLogs('7: x = 6');
        $this->assertInLogs('8: x = 6');
    }
}
