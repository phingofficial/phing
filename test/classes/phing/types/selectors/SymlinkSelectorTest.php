<?php

/**
 * Class SymlinkSelectorTest
 *
 * Test cases for symlink selectors.
 *
 * @requires OS Linux
 */
class SymlinkSelectorTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/selectors/SymlinkSelectorTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testAsFalseConditions()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertyUnset('unset');
    }

    public function testAsTrueConditions()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertPropertySet('selected');
    }
}
