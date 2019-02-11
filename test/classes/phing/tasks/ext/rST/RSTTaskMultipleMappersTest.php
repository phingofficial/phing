<?php

/**
 * Unit test for reStructuredText rendering task.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link       http://www.phing.info/
 */

/**
 * Unit test for reStructuredText rendering task.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link       http://www.phing.info/
 */
class RSTTaskMultipleMappersTest extends BuildFileTest
{
    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Cannot define more than one mapper
     */
    public function testMultipleMappers()
    {
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/rst/build-error-multiple-mappers.xml'
        );

        $this->executeTarget(__FUNCTION__);
    }
}
