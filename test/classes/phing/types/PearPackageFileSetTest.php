<?php
require_once 'phing/BuildFileTest.php';
require_once 'phing/types/PearPackageFileSet.php';

class PearPackageFileSetTest extends BuildFileTest 
{ 
    public function setUp() 
    {
        error_reporting(error_reporting() & ~E_DEPRECATED);
    }

    public function testGetDirectoryScannerConsoleGetopt()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');
        $ds = $ppfs->getDirectoryScanner(new Project());

        $arFiles = $ds->getIncludedFiles();
        $this->assertInternalType(
            'array', $arFiles, 'getIncludedFiles returned no array'
        );
        $this->assertEquals(1, count($arFiles));
        $this->assertContains('Console/Getopt.php', $arFiles);

        $fullPath = $ds->getBaseDir() . reset($arFiles);
        $this->assertTrue(
            file_exists($fullPath), 'File does not exist: ' . $fullPath
        );
    }

    public function testRoleDoc()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('pear.phpunit.de/phpunit');
        $ppfs->setRole('doc');
        $ds = $ppfs->getDirectoryScanner(new Project());

        $arFiles = $ds->getIncludedFiles();
        $this->assertContains('LICENSE', $arFiles);
        foreach ($arFiles as $file) {
            $this->assertNotContains(
                '.php', $file, 'php files should not be in there'
            );
        }
    }

    public function testGetDir()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');
        $ppfs->getDirectoryScanner(new Project());

        $dir = $ppfs->getDir();
        $this->assertTrue(
            file_exists($dir), 'Directory does not exist: ' . $dir
        );
        $this->assertTrue(
            is_dir($dir), '$dir is not a directory: ' . $dir
        );
    }

    public function testGetDirWithoutScanner()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');

        $dir = $ppfs->getDir();
        $this->assertTrue(
            file_exists($dir), 'Directory does not exist: ' . $dir
        );
        $this->assertTrue(
            is_dir($dir), '$dir is not a directory: ' . $dir
        );
    }

    /**
     * @expectedException BuildException
     * @expectedExceptionMessage Invalid package name
     */
    public function testSetPackageInvalid()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('pear.php.net/console_getopt/thisiswrong');
    }
}
?>