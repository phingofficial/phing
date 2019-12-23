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
 * Testcases for phing.types.PearPackageFileSet
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.types
 */
class PearPackageFileSetTest extends BuildFileTest
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
        error_reporting(error_reporting() & ~E_DEPRECATED & ~E_STRICT);
    }

    /**
     * @return void
     */
    public function testGetDirectoryScannerConsoleGetopt(): void
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');
        $ds = $ppfs->getDirectoryScanner(new Project());

        $arFiles = $ds->getIncludedFiles();
        if (method_exists($this, 'assertIsArray')) {
            $this->assertIsArray($arFiles, 'getIncludedFiles returned no array');
        } else {
            $this->assertInternalType('array', $arFiles, 'getIncludedFiles returned no array');
        }
        $this->assertEquals(1, count($arFiles));
        $this->assertContains('Console' . DIRECTORY_SEPARATOR . 'Getopt.php', $arFiles);

        $fullPath = $ds->getBaseDir() . reset($arFiles);
        $this->assertTrue(
            file_exists($fullPath),
            'File does not exist: ' . $fullPath
        );
    }

    /**
     * @return void
     */
    public function testRoleDoc(): void
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('pear.php.net/Archive_Tar');
        $ppfs->setRole('doc');
        $ds = $ppfs->getDirectoryScanner(new Project());

        $arFiles = $ds->getIncludedFiles();
        $this->assertContains('docs/Archive_Tar.txt', $arFiles);
        foreach ($arFiles as $file) {
            $this->assertNotContains(
                '.php',
                $file,
                'php files should not be in there'
            );
        }
    }

    /**
     * @return void
     */
    public function testGetDir(): void
    {
        $proj = new Project();
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');
        $ppfs->getDirectoryScanner($proj);

        $dir = $ppfs->getDir($proj);
        $this->assertTrue(
            file_exists($dir),
            'Directory does not exist: ' . $dir
        );
        $this->assertTrue(
            is_dir($dir),
            '$dir is not a directory: ' . $dir
        );
    }

    /**
     * @return void
     */
    public function testGetDirWithoutScanner(): void
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');

        $dir = $ppfs->getDir(new Project());
        $this->assertTrue(
            file_exists($dir),
            'Directory does not exist: ' . $dir
        );
        $this->assertTrue(
            is_dir($dir),
            '$dir is not a directory: ' . $dir
        );
    }

    /**
     * @return void
     */
    public function testSetPackageInvalid(): void
    {
        $ppfs = new PearPackageFileSet();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Invalid package name');

        $ppfs->setPackage('pear.php.net/console_getopt/thisiswrong');
    }
}
