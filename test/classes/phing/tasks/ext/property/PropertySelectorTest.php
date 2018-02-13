<?php

/**
 * Tests the PropertySelector Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class PropertySelectorTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/property/PropertySelectorTest.xml'
        );
    }

    public function testPropertySelector()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('pack.list', 'ABC,DEF,GHI,JKL');
    }
}
