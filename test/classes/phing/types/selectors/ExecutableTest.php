<?php

/**
 * Class ExecutableTest
 *
 * Test cases for isExecutable selectors.
 * @requires OS Linux
 */
class ExecutableTest extends BuildFileTest
{
    public function setUp()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/ExecutableTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown()
    {
        $this->executeTarget('clean');
    }

    public function testExecutable()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('selected');
    }

    public function testUnexecutable()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('unset');
    }
}
