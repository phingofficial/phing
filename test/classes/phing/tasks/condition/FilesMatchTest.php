<?php

use Phing\Test\AbstractBuildFileTest;

/**
 * Tests the FilesMatch Condition
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class FilesMatchTest extends AbstractBuildFileTest
{

    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/FilesMatchTest.xml'
        );
    }

    public function testFileMatches()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('matches');
    }

    public function testNoFileMatches()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('unset');
    }
}
