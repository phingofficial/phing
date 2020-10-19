<?php

/**
 * Tests FileSizeTask
 *
 * @author  Jawira Portugal <dev@tugal.be>
 * @package phing.tasks.system
 * @license LGPL
 * @license https://github.com/phingofficial/phing/blob/master/LICENSE
 */
class FileSizeTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/ext/FileSizeTaskTest.xml');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testSimpleCase()
    {
        $this->getProject()->setProperty('dummy.size', '1K');
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('1024 B');
        $this->assertPropertyEquals('filesize', 1024);
    }

    public function testPropertyNameAttribute()
    {
        $this->getProject()->setProperty('dummy.size', '2K');
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('2048 B');
        $this->assertPropertyEquals('my-filesize', 2048);
    }

    /**
     * @dataProvider unitAttributeProvider
     */
    public function testUnitAttribute($dummySize, $filesizeUnit, $logVerbose, $logInfo, $expectedSize)
    {
        $this->getProject()->setProperty('dummy.size', $dummySize);
        $this->getProject()->setProperty('filesize.unit', $filesizeUnit);
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs($logVerbose, Project::MSG_VERBOSE);
        $this->assertInLogs($logInfo, Project::MSG_INFO);
        $this->assertPropertyEquals('filesize', $expectedSize);
    }

    public function unitAttributeProvider()
    {
        return [
            ['1K', 'b', '1024 B', '1024 B', 1024],
            ['13K', 'B', '13312 B', '13312 B', 13312],
            ['13K', 'k', '13312 B', '13 K', 13],
            ['517K', 'M', '529408 B', '0.5 M', 0.50],
            ['500K', 'm', '512000 B', '0.49 M', 0.49],
            ['10M', 'G', '10485760 B', '0.01 G', 0.01],
            ['2K', 'G', '2048 B', '0 G', 0],
            ['2K', 't', '2048 B', '0 T', 0],
            ['2K', 'P', '2048 B', '0 P', 0],
        ];
    }

    public function testExceptionFileNotSet()
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, 'File attribute was not set', 'Input file not specified');
    }

    public function testExceptionInvalidFile()
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, 'File is set, but non-existent', 'Input file does not exist or is not readable: invalid-file');
    }

    public function testExceptionInvalidUnit()
    {
        $this->getProject()->setProperty('dummy.size', '1K');
        $this->expectBuildExceptionContaining(__FUNCTION__, 'The unit is not a valid one', 'Invalid unit: foo');
    }

    public function testExceptionEmptyUnit()
    {
        $this->getProject()->setProperty('dummy.size', '1K');
        $this->expectBuildExceptionContaining(__FUNCTION__, 'The unit attribute is empty', 'Invalid unit: ');
    }

    public function testExceptionEmptyProperty()
    {
        $this->getProject()->setProperty('dummy.size', '1K');
        $this->expectBuildExceptionContaining(__FUNCTION__, 'Empty string (or "0") is passed to propertyName attribute', 'Property name cannot be empty');
    }
}
