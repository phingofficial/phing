<?php

/**
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 * 
 * @package phing.util
 */
require_once 'phing/BuildFileTest.php';
require_once 'phing/util/PearPackageScanner.php';

/**
 * Testcases for phing.util.PearPackageScanner
 * 
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.util
 */
class PearPackageScannerTest extends BuildFileTest 
{ 
    public function setUp() 
    {
        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_STRICT);
    }
    
    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
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

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
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
