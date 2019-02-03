<?php

/**
 * Tests the Dirname Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class DirnameTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/DirnameTest.xml'
        );
    }

    public function testDirnameSetToPhingRoot()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('dirname', $this->getProject()->getProperty('phing.home'));
    }
}
