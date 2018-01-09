<?php

/**
 * Class DifferentSelectorTest
 *
 * Test cases for different selectors.
 */
class ContainsRegexpSelectorTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/ContainsRegexpSelectorTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown()
    {
        $this->executeTarget('clean');
    }

    public function testContainsRegexpSelector()
    {
        $this->executeTarget(__FUNCTION__);
        $project = $this->getProject();
        $result = $project->getProperty('result');
        $this->assertFileNotExists($result . '/shouldnotcopy.txt');
        $this->assertFileExists($result . '/shouldcopy.txt');
    }
}
