<?php

/**
 * Tests the SortList Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class SortListTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/property/SortListTest.xml'
        );
    }

    public function testSortList()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('my.sorted.list', 't,u,v,w,x,y,z');
    }

    public function testDelimFlags()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('my.sorted.list', 't;U;v;w;X;y;z');
    }

    public function testRef()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyEquals('my.sorted.list', 'U;X;t;v;w;y;z');
    }
}
