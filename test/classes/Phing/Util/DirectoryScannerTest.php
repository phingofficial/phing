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

/**
 * Testcases for phing.util.DirectoryScanner
 *
 * Based on org.apache.tools.ant.DirectoryScannerTest
 *
 * @see     http://svn.apache.org/viewvc/ant/core/trunk/src/tests/junit/org/apache/tools/ant/DirectoryScannerTest.java
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.util
 */
class DirectoryScannerTest extends BuildFileTest
{
    private $basedir = "";

    public function setUp(): void
    {
        $this->basedir = PHING_TEST_BASE . "/etc/Util/tmp";
        $this->configureProject(PHING_TEST_BASE . "/etc/Util/directoryscanner.xml");
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("cleanup");
    }

    public function testErrorOnMissingDir()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir . '/THIS_DOES_NOT_EXIST');
        $ds->setErrorOnMissingDir(true);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessageRegExp('/basedir (.*)THIS_DOES_NOT_EXIST does not exist\./');

        $ds->scan();
    }

    public function testNoErrorOnMissingDir()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir . '/THIS_DOES_NOT_EXIST');
        $ds->scan();
    }

    public function test1()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["alpha"]);
        $ds->scan();

        $this->compareFiles($ds, [], ["alpha"]);
    }

    public function test2()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["alpha/"]);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                "alpha/beta/beta.xml",
                "alpha/beta/gamma/gamma.xml"
            ],
            [
                "alpha",
                "alpha/beta",
                "alpha/beta/gamma"
            ]
        );
    }

    public function test3()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                "alpha/beta/beta.xml",
                "alpha/beta/gamma/gamma.xml"
            ],
            [
                "",
                "alpha",
                "alpha/beta",
                "alpha/beta/gamma"
            ]
        );
    }

    public function testFullPathMatchesCaseSensitive()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["alpha/beta/gamma/GAMMA.XML"]);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    public function testFullPathMatchesCaseInsensitive()
    {
        $ds = new DirectoryScanner();
        $ds->setCaseSensitive(false);
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["alpha/beta/gamma/GAMMA.XML"]);
        $ds->scan();

        $this->compareFiles($ds, ["alpha/beta/gamma/gamma.xml"], []);
    }

    public function test2ButCaseInsensitive()
    {
        $ds = new DirectoryScanner();
        $ds->setCaseSensitive(false);
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["ALPHA/"]);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                "alpha/beta/beta.xml",
                "alpha/beta/gamma/gamma.xml"
            ],
            [
                "alpha",
                "alpha/beta",
                "alpha/beta/gamma"
            ]
        );
    }

    public function testExcludeOneFile()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["**/*.xml"]);
        $ds->setExcludes(["alpha/beta/b*xml"]);
        $ds->scan();

        $this->compareFiles($ds, ["alpha/beta/gamma/gamma.xml"], []);
    }

    public function testExcludeHasPrecedence()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["alpha/**"]);
        $ds->setExcludes(["alpha/**"]);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    public function testAlternateIncludeExclude()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(["alpha/**", "alpha/beta/gamma/**"]);
        $ds->setExcludes(["alpha/beta/**"]);
        $ds->scan();

        $this->compareFiles($ds, [], ["alpha"]);
    }

    public function testAlternateExcludeInclude()
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setExcludes(["alpha/**", "alpha/beta/gamma/**"]);
        $ds->setIncludes(["alpha/beta/**"]);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    public function testChildrenOfExcludedDirectory()
    {
        $this->executeTarget("children-of-excluded-dir-setup");

        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setExcludes(["alpha/**"]);
        $ds->scan();

        $this->compareFiles($ds, ["delta/delta.xml"], ["", "delta"]);

        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setExcludes(["alpha"]);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                "alpha/beta/beta.xml",
                "alpha/beta/gamma/gamma.xml",
                "delta/delta.xml"
            ],
            [
                "",
                "alpha/beta",
                "alpha/beta/gamma",
                "delta"
            ]
        );
    }

    public function testAbsolute1()
    {
        $base = $this->getProject()->getBasedir();
        $tmpdir = substr($this->replaceSeparator($base->getAbsolutePath()) . "/tmp", $base->getPrefixLength());
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget("extended-setup");

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes([$tmpdir . "/**/*"]);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                $tmpdir . "/alpha/beta/beta.xml",
                $tmpdir . "/alpha/beta/gamma/gamma.xml",
                $tmpdir . "/delta/delta.xml"
            ],
            [
                $tmpdir . "/alpha",
                $tmpdir . "/alpha/beta",
                $tmpdir . "/alpha/beta/gamma",
                $tmpdir . "/delta"
            ]
        );
    }

    public function testAbsolute2()
    {
        $base = $this->getProject()->getBasedir();
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget("setup");

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes(["alpha/**", "alpha/beta/gamma/**"]);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    public function testAbsolute3()
    {
        $base = $this->getProject()->getBasedir();
        $tmpdir = substr($this->replaceSeparator($base->getAbsolutePath()) . "/tmp", $base->getPrefixLength());
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget("extended-setup");

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes([$tmpdir . "/**/*"]);
        $ds->setExcludes(["**/alpha", "**/delta/*"]);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                $tmpdir . "/alpha/beta/beta.xml",
                $tmpdir . "/alpha/beta/gamma/gamma.xml"
            ],
            [
                $tmpdir . "/alpha/beta",
                $tmpdir . "/alpha/beta/gamma",
                $tmpdir . "/delta"
            ]
        );
    }

    public function testAbsolute4()
    {
        $base = $this->getProject()->getBasedir();
        $tmpdir = substr($this->replaceSeparator($base->getAbsolutePath()) . "/tmp", $base->getPrefixLength());
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget("extended-setup");

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes([$tmpdir . "/alpha/beta/**/*", $tmpdir . "/delta/*"]);
        $ds->setExcludes(["**/beta.xml"]);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                $tmpdir . "/alpha/beta/gamma/gamma.xml",
                $tmpdir . "/delta/delta.xml"
            ],
            [$tmpdir . "/alpha/beta/gamma"]
        );
    }

    /**
     * Inspired by http://www.phing.info/trac/ticket/137
     */
    public function testMultipleExcludes()
    {
        $this->executeTarget("multiple-setup");

        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir . "/echo");
        $ds->setIncludes(["**"]);
        $ds->setExcludes(["**/.gitignore", ".svn/", ".git/", "cache/", "build.xml", "a/a.xml"]);
        $ds->scan();

        $this->compareFiles($ds, ["b/b.xml"], ["", "a", "b"]);
    }

    protected function replaceSeparator($item)
    {
        $fs = FileSystem::getFileSystem();

        return str_replace($fs->getSeparator(), '/', $item);
    }

    protected function compareFiles(DirectoryScanner $ds, $expectedFiles, $expectedDirectories)
    {
        $includedFiles = $ds->getIncludedFiles();
        $includedDirectories = $ds->getIncludedDirectories();

        if (count($includedFiles)) {
            $includedFiles = array_map([$this, 'replaceSeparator'], $includedFiles);
            natsort($includedFiles);
            $includedFiles = array_values($includedFiles);
        }

        if (count($includedDirectories)) {
            $includedDirectories = array_map([$this, 'replaceSeparator'], $includedDirectories);
            natsort($includedDirectories);
            $includedDirectories = array_values($includedDirectories);
        }

        $this->assertEquals($includedFiles, $expectedFiles);
        $this->assertEquals($includedDirectories, $expectedDirectories);
    }
}
