<?php

/**
 * Tests the PropertyCopy Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class PropertyCopyTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/property/PropertyCopyTest.xml'
        );
    }

    public function testPropertyCopy()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('displayName', 'My Organiziation');
    }
}
