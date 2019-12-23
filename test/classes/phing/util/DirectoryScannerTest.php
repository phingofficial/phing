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
 * Testcases for phing.util.DirectoryScanner
 *
 * Based on org.apache.tools.ant.DirectoryScannerTest
 *
 * @see     http://svn.apache.org/viewvc/ant/core/trunk/src/tests/junit/org/apache/tools/ant/DirectoryScannerTest.java
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.util
 */
class DirectoryScannerTest extends BuildFileTest
{
    /**
     * @var string
     */
    private $basedir = '';

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->basedir = PHING_TEST_BASE . '/etc/util/tmp';
        $this->configureProject(PHING_TEST_BASE . '/etc/util/directoryscanner.xml');
        $this->executeTarget('setup');
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->executeTarget('cleanup');
    }

    /**
     * @return void
     */
    public function testErrorOnMissingDir(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir . '/THIS_DOES_NOT_EXIST');
        $ds->setErrorOnMissingDir(true);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessageRegExp('/basedir (.*)THIS_DOES_NOT_EXIST does not exist\./');

        $ds->scan();
    }

    /**
     * @return void
     */
    public function testNoErrorOnMissingDir(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir . '/THIS_DOES_NOT_EXIST');
        $ds->scan();

        $this->assertEquals(1, 1); // increase number of positive assertions
    }

    /**
     * @return void
     */
    public function test1(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['alpha']);
        $ds->scan();

        $this->compareFiles($ds, [], ['alpha']);
    }

    /**
     * @return void
     */
    public function test2(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['alpha/']);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                'alpha/beta/beta.xml',
                'alpha/beta/gamma/gamma.xml',
            ],
            [
                'alpha',
                'alpha/beta',
                'alpha/beta/gamma',
            ]
        );
    }

    /**
     * @return void
     */
    public function test3(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                'alpha/beta/beta.xml',
                'alpha/beta/gamma/gamma.xml',
            ],
            [
                '',
                'alpha',
                'alpha/beta',
                'alpha/beta/gamma',
            ]
        );
    }

    /**
     * @return void
     */
    public function testFullPathMatchesCaseSensitive(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['alpha/beta/gamma/GAMMA.XML']);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    /**
     * @return void
     */
    public function testFullPathMatchesCaseInsensitive(): void
    {
        $ds = new DirectoryScanner();
        $ds->setCaseSensitive(false);
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['alpha/beta/gamma/GAMMA.XML']);
        $ds->scan();

        $this->compareFiles($ds, ['alpha/beta/gamma/gamma.xml'], []);
    }

    /**
     * @return void
     */
    public function test2ButCaseInsensitive(): void
    {
        $ds = new DirectoryScanner();
        $ds->setCaseSensitive(false);
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['ALPHA/']);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                'alpha/beta/beta.xml',
                'alpha/beta/gamma/gamma.xml',
            ],
            [
                'alpha',
                'alpha/beta',
                'alpha/beta/gamma',
            ]
        );
    }

    /**
     * @return void
     */
    public function testExcludeOneFile(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['**/*.xml']);
        $ds->setExcludes(['alpha/beta/b*xml']);
        $ds->scan();

        $this->compareFiles($ds, ['alpha/beta/gamma/gamma.xml'], []);
    }

    /**
     * @return void
     */
    public function testExcludeHasPrecedence(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['alpha/**']);
        $ds->setExcludes(['alpha/**']);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    /**
     * @return void
     */
    public function testAlternateIncludeExclude(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setIncludes(['alpha/**', 'alpha/beta/gamma/**']);
        $ds->setExcludes(['alpha/beta/**']);
        $ds->scan();

        $this->compareFiles($ds, [], ['alpha']);
    }

    /**
     * @return void
     */
    public function testAlternateExcludeInclude(): void
    {
        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setExcludes(['alpha/**', 'alpha/beta/gamma/**']);
        $ds->setIncludes(['alpha/beta/**']);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    /**
     * @return void
     */
    public function testChildrenOfExcludedDirectory(): void
    {
        $this->executeTarget('children-of-excluded-dir-setup');

        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setExcludes(['alpha/**']);
        $ds->scan();

        $this->compareFiles($ds, ['delta/delta.xml'], ['', 'delta']);

        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir);
        $ds->setExcludes(['alpha']);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                'alpha/beta/beta.xml',
                'alpha/beta/gamma/gamma.xml',
                'delta/delta.xml',
            ],
            [
                '',
                'alpha/beta',
                'alpha/beta/gamma',
                'delta',
            ]
        );
    }

    /**
     * @return void
     */
    public function testAbsolute1(): void
    {
        $base   = $this->getProject()->getBasedir();
        $tmpdir = substr($this->replaceSeparator($base->getAbsolutePath()) . '/tmp', $base->getPrefixLength());
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget('extended-setup');

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes([$tmpdir . '/**/*']);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                $tmpdir . '/alpha/beta/beta.xml',
                $tmpdir . '/alpha/beta/gamma/gamma.xml',
                $tmpdir . '/delta/delta.xml',
            ],
            [
                $tmpdir . '/alpha',
                $tmpdir . '/alpha/beta',
                $tmpdir . '/alpha/beta/gamma',
                $tmpdir . '/delta',
            ]
        );
    }

    /**
     * @return void
     */
    public function testAbsolute2(): void
    {
        $base   = $this->getProject()->getBasedir();
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget('setup');

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes(['alpha/**', 'alpha/beta/gamma/**']);
        $ds->scan();

        $this->compareFiles($ds, [], []);
    }

    /**
     * @return void
     */
    public function testAbsolute3(): void
    {
        $base   = $this->getProject()->getBasedir();
        $tmpdir = substr($this->replaceSeparator($base->getAbsolutePath()) . '/tmp', $base->getPrefixLength());
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget('extended-setup');

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes([$tmpdir . '/**/*']);
        $ds->setExcludes(['**/alpha', '**/delta/*']);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                $tmpdir . '/alpha/beta/beta.xml',
                $tmpdir . '/alpha/beta/gamma/gamma.xml',
            ],
            [
                $tmpdir . '/alpha/beta',
                $tmpdir . '/alpha/beta/gamma',
                $tmpdir . '/delta',
            ]
        );
    }

    /**
     * @return void
     */
    public function testAbsolute4(): void
    {
        $base   = $this->getProject()->getBasedir();
        $tmpdir = substr($this->replaceSeparator($base->getAbsolutePath()) . '/tmp', $base->getPrefixLength());
        $prefix = substr($base->getAbsolutePath(), 0, $base->getPrefixLength());

        $this->executeTarget('extended-setup');

        $ds = new DirectoryScanner();
        $ds->setBasedir($prefix);
        $ds->setIncludes([$tmpdir . '/alpha/beta/**/*', $tmpdir . '/delta/*']);
        $ds->setExcludes(['**/beta.xml']);
        $ds->scan();

        $this->compareFiles(
            $ds,
            [
                $tmpdir . '/alpha/beta/gamma/gamma.xml',
                $tmpdir . '/delta/delta.xml',
            ],
            [$tmpdir . '/alpha/beta/gamma']
        );
    }

    /**
     * Inspired by http://www.phing.info/trac/ticket/137
     *
     * @return void
     */
    public function testMultipleExcludes(): void
    {
        $this->executeTarget('multiple-setup');

        $ds = new DirectoryScanner();
        $ds->setBasedir($this->basedir . '/echo');
        $ds->setIncludes(['**']);
        $ds->setExcludes(['**/.gitignore', '.svn/', '.git/', 'cache/', 'build.xml', 'a/a.xml']);
        $ds->scan();

        $this->compareFiles($ds, ['b/b.xml'], ['', 'a', 'b']);
    }

    /**
     * @param string|array $item
     *
     * @return string|array
     *
     * @throws IOException
     */
    protected function replaceSeparator($item)
    {
        $fs = FileSystem::getFileSystem();

        return str_replace($fs->getSeparator(), '/', $item);
    }

    /**
     * @param DirectoryScanner $ds
     * @param array            $expectedFiles
     * @param array            $expectedDirectories
     *
     * @return void
     */
    protected function compareFiles(DirectoryScanner $ds, array $expectedFiles, array $expectedDirectories): void
    {
        $includedFiles       = $ds->getIncludedFiles();
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
