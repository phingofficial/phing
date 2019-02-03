<?php

/**
 * Class DifferentSelectorTest
 *
 * Test cases for different selectors.
 */
class DifferentSelectorTest extends BuildFileTest
{
    public function setUp(): void    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/DifferentSelectorTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void    {
        $this->executeTarget('clean');
    }

    public function testSameTime()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $result = $project->getProperty('result');
        $this->assertFileNotExists($result . '/a.txt');
    }

    public function testDifferentTime()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $result = $project->getProperty('result');
        $this->assertFileExists($result . '/b.txt');
    }
}
