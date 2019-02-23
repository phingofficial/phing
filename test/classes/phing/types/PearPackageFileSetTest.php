<?php

/**
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

/**
 * Testcases for phing.types.PearPackageFileSet
 *
 * @author  Christian Weiske <cweiske@cweiske.de>
 * @package phing.types
 */
class PearPackageFileSetTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (!class_exists('PEAR_Config')) {
            $this->markTestSkipped("This test requires PEAR to be installed");
        }

        if (defined('HHVM_VERSION')) {
            $this->markTestSkipped("PEAR tests do not run on HHVM");
        }

        //needed for PEAR's Config and Registry classes
        error_reporting(error_reporting() & ~E_DEPRECATED & ~E_STRICT);
    }

    public function testGetDirectoryScannerConsoleGetopt()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');
        $ds = $ppfs->getDirectoryScanner(new Project());

        $arFiles = $ds->getIncludedFiles();
        $this->assertIsArray($arFiles, 'getIncludedFiles returned no array');
        $this->assertCount(1, $arFiles);
        $this->assertContains('Console' . DIRECTORY_SEPARATOR . 'Getopt.php', $arFiles);

        $fullPath = $ds->getBasedir()->getAbsolutePath() . reset($arFiles);
        $this->assertFileExists($fullPath, 'File does not exist: ' . $fullPath);
    }

    public function testRoleDoc()
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

    public function testGetDir()
    {
        $proj = new Project();
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');
        $ppfs->getDirectoryScanner($proj);

        $dir = $ppfs->getDir($proj);
        $this->assertTrue(
            $dir->exists(),
            'Directory does not exist: ' . $dir
        );
        $this->assertTrue(
            $dir->exists(),
            '$dir is not a directory: ' . $dir
        );
    }

    public function testGetDirWithoutScanner()
    {
        $ppfs = new PearPackageFileSet();
        $ppfs->setPackage('console_getopt');
        $ppfs->setRole('php');

        $dir = $ppfs->getDir(new Project());
        $this->assertTrue(
            $dir->exists(),
            'Directory does not exist: ' . $dir
        );
        $this->assertTrue(
            $dir->exists(),
            '$dir is not a directory: ' . $dir
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
