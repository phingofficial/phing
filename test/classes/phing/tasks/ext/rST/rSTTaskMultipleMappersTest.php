<?php
require_once 'phing/BuildFileTest.php';

class rSTTaskMultipleMappersTest extends BuildFileTest 
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
    }

}