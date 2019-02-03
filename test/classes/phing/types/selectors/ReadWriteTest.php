<?php

/**
 * Class ReadWriteTest
 *
 * Test cases for isReadable/isWritable selectors.
 */
class ReadWriteTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/ReadWriteTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void    {
        $this->executeTarget('clean');
    }

    public function testReadable()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('output');
        $file = $project->getProperty('file');
        $this->assertTrue(is_readable(sprintf('%s/%s', $output, $file)));
    }

    public function testWritable()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('output');
        $file = $project->getProperty('file');
        $this->assertTrue(is_writable(sprintf('%s/%s', $output, $file)));
    }

    public function testUnwritable()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $output = $project->getProperty('output');
        $file = $project->getProperty('file');
        $this->assertFalse(is_writable(sprintf('%s/%s', $output, $file)));
    }
}
