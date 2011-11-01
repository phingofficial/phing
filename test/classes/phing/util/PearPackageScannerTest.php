<?php

require_once 'phing/BuildFileTest.php';
require_once 'phing/util/PearPackageScanner.php';

class PearPackageScannerTest extends BuildFileTest 
{ 

    public function testLoadPackageInfo()
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setPackage('console_getopt');

        $ref = new ReflectionClass($ppfs);
        $method = $ref->getMethod('loadPackageInfo');
        $method->setAccessible(true);
        $packageInfo = $method->invoke($ppfs);

        $this->assertNotNull($packageInfo, 'Package info is null');
        $this->assertInstanceOf('PEAR_PackageFile_v2', $packageInfo);
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage PEAR package pear.php.net/this_package_does_not_exist does not exist
     */
    public function testLoadPackageInfoNonexistingPackage()
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setPackage('this_package_does_not_exist');

        $ref = new ReflectionClass($ppfs);
        $method = $ref->getMethod('loadPackageInfo');
        $method->setAccessible(true);
        $packageInfo = $method->invoke($ppfs);
    }

    /**
     * @expectedException BuildException
     */
    public function testSetRoleEmpty()
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setRole(null);
    }

    public function testScanRoleDocCorrectDirectory()
    {
        $pps = new PearPackageScanner();
        $pps->setChannel('pear.phpunit.de');
        $pps->setPackage('phpunit');
        $pps->setRole('doc');
        $pps->scan();

        $arFiles = $pps->getIncludedFiles();
        $basedir = $pps->getBaseDir();
        $this->assertContains('LICENSE', $arFiles);
        foreach ($arFiles as $file) {
            $fullpath = $basedir . $file;
            $this->assertTrue(
                file_exists($fullpath),
                'File does not exist: ' . $file . ' at ' . $fullpath
            );
        }

    }

    /**
     * @expectedException BuildException
     */
    public function testSetConfigNonexistingFile()
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setConfig('/this/file/does/not/really/exist');
    }

    public function testGetIncludedFiles()
    {
        $this->markTestIncomplete();
    }

    public function testGetIncludedDirectories()
    {
        $this->markTestIncomplete();
    }

    public function testGetBaseDir()
    {
        $this->markTestIncomplete();
    }

    public function testScan()
    {
        $this->markTestIncomplete();
    }

}
?>