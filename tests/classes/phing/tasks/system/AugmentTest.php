<?php

use Phing\Support\BuildFileTest;
use Phing\Type\FileSet;

/**
 * Tests the AugmentReference Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system
 */
class AugmentTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/system/AugmentTest.xml'
        );
        $this->executeTarget(__FUNCTION__);
        /** @var FileSet $fs */
        $fs = $this->getProject()->getReference('input-fs');
        $this->assertEquals(3, $fs->getIterator()->count());
    }

    public function tearDown(): void
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testAugmentAttribute()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(2, $this->getProject()->getReference('input-fs')->getIterator()->count());
    }

    public function testAugmentElement()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertEquals(1, $this->getProject()->getReference('input-fs')->getIterator()->count());
    }

    public function testNoref()
    {
        $this->expectSpecificBuildException(
            __FUNCTION__,
            'an unknown reference was found.',
            'Unknown reference "nosuchreference"'
        );
    }

    public function testIdNotSet()
    {
        $this->expectSpecificBuildException(
            __FUNCTION__,
            "the augment attribute 'id' is set.",
            "augment attribute 'id' unset"
        );
    }

    public function testIllegalAttribute()
    {
        $this->expectSpecificBuildException(
            __FUNCTION__,
            'it does support unsupported attribute',
            FileSet::class . " doesn't support the 'filesetwillmostlikelyneversupportthisattribute' attribute."
        );
    }

    public function testIllegalElement()
    {
        $this->expectSpecificBuildException(
            __FUNCTION__,
            'it does support unsupported element',
            FileSet::class . " doesn't support the 'filesetwillmostlikelyneversupportthiselement' creator/adder."
        );
    }
}
