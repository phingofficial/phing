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
    protected $backupGlobals = false;

    public function setUp()
    {
        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
    }

    public function testLoadPackageInfo()
    {
        if (version_compare(PHP_VERSION, '5.3.2') < 0) {
            $this->markTestSkipped("Need PHP 5.3.2+ for this test");
        }

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
        if (version_compare(PHP_VERSION, '5.3.2') < 0) {
            $this->markTestSkipped("Need PHP 5.3.2+ for this test");
        }

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
        $pps->setChannel('pear.php.net');
        $pps->setPackage('Archive_Tar');
        $pps->setRole('doc');
        $pps->scan();

        $arFiles = $pps->getIncludedFiles();
        $basedir = $pps->getBaseDir();
        $this->assertContains('docs/Archive_Tar.txt', $arFiles);
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

    /**
     * @expectedException BuildException
     */
    public function testSetDescFileNonexistingFile()
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setDescFile('/this/file/does/not/exist');
    }

    /**
     * baseinstalldir attribute needs to be taken into account.
     */
    public function testScanBaseInstallDir()
    {
        $pps = new PearPackageScanner();
        $pps->setDescFile(
            PHING_TEST_BASE . '/etc/types/package_Console_Table-1.2.0.xml'
        );
        $pps->setRole('php');
        $pps->scan();

        $arFiles = $pps->getIncludedFiles();

        $this->assertEquals(
            $arFiles,
            array(
                'Console/Table.php'
            )
        );
    }

    /**
     * install_as-attribute needs to be taken into account.
     *
     * We need to use a serialized package info here since some
     * properties we need are only available in the registry,
     * not in the package file itself.
     */
    public function testScanInstallAs()
    {
        if (version_compare(PHP_VERSION, '5.3.0', '<')) {
            return $this->markTestSkipped('Test works on PHP 5.3 only');
        }

        $pkgInfoFile = __DIR__ . '/../../../etc/types/'
            . 'packageInfo_Services_Linkback-0.2.0.ser.dat';

        $pps = new PearPackageScanner();
        $prop = new ReflectionProperty('PearPackageScanner', 'packageInfo');
        $prop->setAccessible(true);
        $prop->setValue($pps, unserialize(file_get_contents($pkgInfoFile)));
        $pps->setRole('php');
        $pps->scan();

        $arFiles = $pps->getIncludedFiles();
        $this->assertContains(
            'PEAR2/Services/Linkback/Response/Ping.php',
            $arFiles
        );
    }

}
