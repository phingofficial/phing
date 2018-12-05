<?php

/**
 * Tests the DependSet Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class DependSetTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/dependset.xml'
        );
    }

    public function tearDown()
    {
        $this->executeTarget('cleanup');
    }

    public function test1()
    {
        $this->expectBuildException(__FUNCTION__, "At least one <srcfileset> or <srcfilelist> element must be set");
    }

    public function test2()
    {
        $this->expectBuildException(__FUNCTION__,
            "At least one <targetfileset> or <targetfilelist> element must be set");
    }

    public function test3()
    {
        $this->expectBuildException(__FUNCTION__, "At least one <srcfileset> or <srcfilelist> element must be set");
    }

    public function test4()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function test5()
    {
        $this->executeTarget(__FUNCTION__);
        $f = new PhingFile($this->getProjectDir(), 'older.tmp');
        if ($f->exists()) {
            $this->fail('dependset failed to remove out of date file ' . (string) $f);
        }
    }
}
