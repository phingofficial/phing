<?php
require_once 'phing/BuildFileTest.php';

class pearPackageFileSetBuildTest extends BuildFileTest 
{ 
    public function setUp() 
    {
        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_DEPRECATED);

        $this->configureProject(
            PHING_TEST_BASE . '/etc/types/PearPackageFileSetBuildTest.xml'
        );
    }

    public function testConsoleGetopt()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Console/Getopt.php');
    }

    public function testRoleDoc()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('/LICENSE');
    }

    public function testCopyConsoleGetopt()
    {
        $this->executeTarget(__FUNCTION__);
    }

    public function testCopyMapperConsoleGetopt()
    {
        $this->executeTarget(__FUNCTION__);
    }
}