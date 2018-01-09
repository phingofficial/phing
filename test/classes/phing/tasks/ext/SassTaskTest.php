<?php

class SassTaskTest extends BuildFileTest
{
    protected $object;

    public function setUp()
    {
        $this->configureProject(PHING_TEST_BASE . "/etc/tasks/ext/SassTaskTest.xml");
        $this->object = new SassTask();
    }

    public function testCheckDefaults()
    {
        $this->assertEquals('', $this->object->getPath());
        $this->assertEquals('', $this->object->getOutputpath());
        $this->assertEquals('utf-8', $this->object->getEncoding());
        $this->assertEquals('nested', $this->object->getStyle());
        $this->assertEquals('css', $this->object->getNewext());
        $this->assertFalse($this->object->getTrace());
        $this->assertFalse($this->object->getCheck());
        $this->assertTrue($this->object->getUnixnewlines());
        $this->assertTrue($this->object->getKeepsubdirectories());
        $this->assertTrue($this->object->getRemoveoldext());
        $this->assertEquals(
            'sass',
            $this->object->getExecutable(),
            "Executable is not 'sass'"
        );
        $this->assertEquals(
            '',
            $this->object->getExtfilter(),
            "Extfilter is not ''"
        );
        $this->assertTrue($this->object->getRemoveoldext());
        $this->assertFalse($this->object->getCompressed());
        $this->assertFalse($this->object->getCompact());
        $this->assertFalse($this->object->getExpand());
        $this->assertFalse($this->object->getCrunched());
        $this->assertTrue($this->object->getNested());
    }

    public function testSetStyleCompactViasetStyle()
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compact');
        $this->assertTrue($this->object->getCompact());
        $this->assertEquals('compact', $this->object->getStyle());
        $this->assertEquals('--style compact', $this->object->getFlags());
        $this->assertFalse($this->object->getCompressed());
        $this->assertFalse($this->object->getExpand());
        $this->assertFalse($this->object->getCrunched());
        $this->assertFalse($this->object->getNested());
    }

    public function testSetStyleCompactViaOwnMethod()
    {
        $this->object->setStyle('crunched');
        $this->object->setCompact('yes');
        $this->assertTrue($this->object->getCompact());
        $this->assertEquals('compact', $this->object->getStyle());
        $this->assertEquals('--style compact', $this->object->getFlags());
        $this->assertFalse($this->object->getCompressed());
        $this->assertFalse($this->object->getExpand());
        $this->assertFalse($this->object->getCrunched());
        $this->assertFalse($this->object->getNested());
    }

    public function testSetStyleCompressedViasetStyle()
    {
        $this->object->setStyle('crunched');
        $this->object->setStyle('compressed');
        $this->assertTrue($this->object->getCompressed());
        $this->assertEquals('compressed', $this->object->getStyle());
        $this->assertEquals('--style compressed', $this->object->getFlags());
        $this->assertFalse($this->object->getCompact());
        $this->assertFalse($this->object->getExpand());
        $this->assertFalse($this->object->getCrunched());
        $this->assertFalse($this->object->getNested());
    }

    public function testSetStyleCompressedViaOwnMethod()
    {
        $this->object->setStyle('crunched');
        $this->object->setCompressed('yes');
        $this->assertTrue($this->object->getCompressed());
        $this->assertEquals('compressed', $this->object->getStyle());
        $this->assertEquals('--style compressed', $this->object->getFlags());
        $this->assertFalse($this->object->getCompact());
        $this->assertFalse($this->object->getExpand());
        $this->assertFalse($this->object->getCrunched());
        $this->assertFalse($this->object->getNested());
    }

    public function testNothing()
    {
        $this->executeTarget("nothing");
        $this->assertInLogs(
            "Neither sass nor scssphp are to be used.",
            Project::MSG_ERR
        );
    }

    public function testSetStyleToUnrecognised()
    {
        $this->executeTarget("testSettingUnrecognisedStyle");
        $this->assertInLogs('Style compacted ignored', Project::MSG_INFO);
        $this->assertInLogs(
            "Neither sass nor scssphp are to be used.",
            Project::MSG_ERR
        );
    }

    public function testNoFilesetAndNoFileSet()
    {
        $this->expectBuildExceptionContaining(
            'testNoFilesetAndNoFileSet',
            'testNoFilesetAndNoFileSet',
            "Missing either a nested fileset or attribute 'file'"
        );
    }
}
