<?php
/**
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

declare(strict_types=1);

/**
 * Testcases for phing.util.PearPackageScanner
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.util
 */
class PearPackageScannerTest extends BuildFileTest
{
    /**
     * @return void
     */
    protected function setUp(): void
    {
        if (!class_exists('PEAR_Config')) {
            $this->markTestSkipped('This test requires PEAR to be installed');
        }

        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testLoadPackageInfo(): void
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setPackage('console_getopt');

        $ref    = new ReflectionClass($ppfs);
        $method = $ref->getMethod('loadPackageInfo');
        $method->setAccessible(true);
        $packageInfo = $method->invoke($ppfs);

        $this->assertNotNull($packageInfo, 'Package info is null');
        $this->assertInstanceOf('PEAR_PackageFile_v2', $packageInfo);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    public function testLoadPackageInfoNonexistingPackage(): void
    {
        $ppfs = new PearPackageScanner();
        $ppfs->setPackage('this_package_does_not_exist');

        $ref    = new ReflectionClass($ppfs);
        $method = $ref->getMethod('loadPackageInfo');
        $method->setAccessible(true);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('PEAR package pear.php.net/this_package_does_not_exist does not exist');

        $method->invoke($ppfs);
    }

    /**
     * @return void
     */
    public function testSetRoleEmpty(): void
    {
        $ppfs = new PearPackageScanner();

        $this->expectException(BuildException::class);

        $ppfs->setRole(null);
    }

    /**
     * @return void
     */
    public function testScanRoleDocCorrectDirectory(): void
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
                file_exists($fullpath) || file_exists($fullpath . '.gz'),
                'File does not exist: ' . $file . ' at ' . $fullpath
            );
        }
    }

    /**
     * @return void
     */
    public function testSetConfigNonexistingFile(): void
    {
        $ppfs = new PearPackageScanner();

        $this->expectException(BuildException::class);

        $ppfs->setConfig('/this/file/does/not/really/exist');
    }

    /**
     * @return void
     */
    public function testGetIncludedFiles(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testGetIncludedDirectories(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testGetBaseDir(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testScan(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @return void
     */
    public function testSetDescFileNonexistingFile(): void
    {
        $ppfs = new PearPackageScanner();

        $this->expectException(BuildException::class);

        $ppfs->setDescFile('/this/file/does/not/exist');
    }

    /**
     * baseinstalldir attribute needs to be taken into account.
     *
     * @return void
     */
    public function testScanBaseInstallDir(): void
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
            [
                'Console' . DIRECTORY_SEPARATOR . 'Table.php'
            ]
        );
    }

    /**
     * install_as-attribute needs to be taken into account.
     * We need to use a serialized package info here since some
     * properties we need are only available in the registry,
     * not in the package file itself.
     *
     * @return void
     *
     * @throws ReflectionException
     */
    public function testScanInstallAs(): void
    {
        $pkgInfoFile = __DIR__ . '/../../../etc/types/'
            . 'packageInfo_Services_Linkback-0.2.0.ser.dat';

        $pps  = new PearPackageScanner();
        $prop = new ReflectionProperty('PearPackageScanner', 'packageInfo');
        $prop->setAccessible(true);
        $prop->setValue($pps, unserialize(file_get_contents($pkgInfoFile)));
        $pps->setRole('php');
        $pps->scan();

        $arFiles = $pps->getIncludedFiles();
        $this->assertContains(
            'PEAR2' . DIRECTORY_SEPARATOR
            . 'Services' . DIRECTORY_SEPARATOR
            . 'Linkback' . DIRECTORY_SEPARATOR
            . 'Response' . DIRECTORY_SEPARATOR
            . 'Ping.php',
            $arFiles
        );
    }
}
