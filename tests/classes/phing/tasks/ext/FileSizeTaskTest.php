<?php

use Phing\Project;
use Phing\Support\BuildFileTest;

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
        $this->getProject()->setProperty('dummy.size', '12345B');
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('12345B');
        $this->assertPropertyEquals('filesize', 12345);
    }

    public function testPropertyNameAttribute()
    {
        $this->getProject()->setProperty('dummy.size', '27027K');
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('27675648B');
        $this->assertPropertyEquals('my-filesize', 27675648);
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
            ['1K', 'b', '1024B', '1024B', 1024],
            ['13K', 'B', '13312B', '13312B', 13312],
            ['13K', 'k', '13312B', '13K', 13],
            ['517K', 'M', '529408B', '0.5048828125M', 0.5048828125],
            ['500K', 'm', '512000B', '0.48828125m', 0.48828125],
            ['10M', 'g', '10485760B', '0.009765625g', 0.009765625],
            ['20M', 'G', '20971520B', '0.01953125G', 0.01953125],
            ['20m', 't', '20971520B', '1.9073486328125E-5t', '1.9073486328125E-5'],
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
        $this->expectBuildExceptionContaining(__FUNCTION__, 'The unit is not a valid one', "Invalid unit 'foo'");
    }

    public function testExceptionEmptyUnit()
    {
        $this->getProject()->setProperty('dummy.size', '1K');
        $this->expectBuildExceptionContaining(__FUNCTION__, 'The unit attribute is empty', "Invalid unit ''");
    }

    public function testExceptionEmptyProperty()
    {
        $this->getProject()->setProperty('dummy.size', '1K');
        $this->expectBuildExceptionContaining(__FUNCTION__, 'Empty string (or "0") is passed to propertyName attribute', 'Property name cannot be empty');
    }
}
