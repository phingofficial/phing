<?php

class SassTaskTest extends BuildFileTest
{

    /** @var SassTask */
    private $object;

    /** @var SassTaskAssert */
    private $sassTaskAssert;

    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/sass/SassTaskTest.xml");
        $this->object = new SassTask();
        $this->sassTaskAssert = new SassTaskAssert();
    }

    public function testCheckDefaults(): void
    {
        $this->sassTaskAssert->assertDefaults($this->object);
    }

    public function testSetStyleCompactViaSetStyle(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compact');
        $this->sassTaskAssert->assertCompactStyle($this->object);
    }

    public function testSetStyleCompactViaOwnMethod(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setCompact('yes');
        $this->sassTaskAssert->assertCompactStyle($this->object);
    }

    public function testSetStyleCompressedViaSetStyle(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compressed');
        $this->sassTaskAssert->assertCompressedStyle($this->object);
    }

    public function testSetStyleCompressedViaOwnMethod(): void
    {
        $this->object->setStyle('crunched');
        $this->object->setCompressed('yes');
        $this->sassTaskAssert->assertCompressedStyle($this->object);
    }

    public function testNothing(): void
    {
        $this->executeTarget("nothing");
        $this->assertInLogs(
            "Neither sass nor scssphp are to be used.",
            Project::MSG_ERR
        );
    }

    public function testSetStyleToUnrecognised(): void
    {
        $this->executeTarget("testSettingUnrecognisedStyle");
        $this->assertInLogs('Style compacted ignored', Project::MSG_INFO);
        $this->assertInLogs(
            "Neither sass nor scssphp are to be used.",
            Project::MSG_ERR
        );
    }

    public function testNoFilesetAndNoFileSet(): void
    {
        $this->expectBuildExceptionContaining(
            'testNoFilesetAndNoFileSet',
            'testNoFilesetAndNoFileSet',
            "Missing either a nested fileset or attribute 'file'"
        );
    }
}
