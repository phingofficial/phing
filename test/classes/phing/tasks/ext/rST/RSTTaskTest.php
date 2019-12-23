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
 */

declare(strict_types=1);

/**
 * Unit test for reStructuredText rendering task.
 *
 * PHP version 5
 *
 * @link       http://www.phing.info/
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 */
class RSTTaskTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        //needed for PEAR's System class
        error_reporting(error_reporting() & ~E_STRICT & ~E_DEPRECATED);

        chdir(PHING_TEST_BASE . '/etc/tasks/ext/rst');
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/rst/build.xml'
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        // remove excess file if the test failed
        @unlink(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/single.html');
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     */
    protected function assertPreConditions(): void
    {
        try {
            $this->testGetToolPathHtmlFormat();
        } catch (BuildException $be) {
            $this->markTestSkipped($be->getMessage());
        }
    }

    /**
     * Checks if a given file has been created and unlinks it afterwards.
     *
     * @param string $file relative file path
     *
     * @return void
     */
    protected function assertFileCreated(string $file): void
    {
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/rst/' . $file,
            $file . ' has not been created'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/rst/' . $file);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     *
     * @requires function ReflectionMethod::setAccessible
     */
    public function testGetToolPathFail(): void
    {
        $rt     = new RSTTask();
        $ref    = new ReflectionClass($rt);
        $method = $ref->getMethod('getToolPath');
        $method->setAccessible(true);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('"rst2doesnotexist" not found. Install python-docutils.');

        $method->invoke($rt, 'doesnotexist');
    }

    /**
     * Get the tool path previously set with setToolpath()
     *
     * @return void
     *
     * @throws ReflectionException
     *
     * @requires function ReflectionMethod::setAccessible
     */
    public function testGetToolPathCustom(): void
    {
        $rt = new RSTTask();
        $rt->setToolpath('true'); //mostly /bin/true on unix
        $ref    = new ReflectionClass($rt);
        $method = $ref->getMethod('getToolPath');
        $method->setAccessible(true);
        $this->assertStringContainsString('/true', $method->invoke($rt, 'foo'));
    }

    /**
     * @return void
     */
    public function testSetToolpathNotExisting(): void
    {
        $rt = new RSTTask();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Tool does not exist. Path:');

        $rt->setToolpath('doesnotandwillneverexist');
    }

    /**
     * @return void
     */
    public function testSetToolpathNonExecutable(): void
    {
        $rt = new RSTTask();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Tool not executable. Path:');

        $rt->setToolpath(__FILE__);
    }

    /**
     * @return void
     *
     * @throws ReflectionException
     *
     * @requires function ReflectionMethod::setAccessible
     */
    public function testGetToolPathHtmlFormat(): void
    {
        $rt     = new RSTTask();
        $ref    = new ReflectionClass($rt);
        $method = $ref->getMethod('getToolPath');
        $method->setAccessible(true);
        $this->assertStringContainsString('rst2html', $method->invoke($rt, 'html'));
    }

    /**
     * @return void
     */
    public function testSingleFileParameterFile(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
    }

    /**
     * @return void
     */
    public function testSingleFileParameterFileNoExt(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single-no-ext.html');
    }

    /**
     * @return void
     */
    public function testSingleFileParameterFileFormat(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.3');
    }

    /**
     * @return void
     */
    public function testSingleFileInvalidParameterFormat(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Invalid parameter',
            'Invalid output format "foo", allowed are'
        );
    }

    /**
     * @return void
     */
    public function testSingleFileParameterFileFormatDestination(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single-destination.html');
    }

    /**
     * @return void
     */
    public function testParameterDestinationAsDirectory(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/subdir/files/single.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir/files');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir');
    }

    /**
     * @return void
     */
    public function testParameterDestinationDirectoryWithFileset(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/subdir/files/single.html');
        $this->assertFileCreated('files/subdir/files/two.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir/files');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir');
    }

    /**
     * @return void
     */
    public function testParameterDestinationDirectoryWithFilesetDot(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/subdir/files/single.html');
        $this->assertFileCreated('files/subdir/files/two.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir/files');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir');
    }

    /**
     * @return void
     */
    public function testParameterUptodate(): void
    {
        $this->executeTarget(__FUNCTION__);
        $file = PHING_TEST_BASE . '/etc/tasks/ext/rst/files/single.html';
        $this->assertFileExists($file);
        $this->assertEquals(
            0,
            filesize($file),
            'File size is not 0, which it should have been when'
            . ' rendering was skipped'
        );
        unlink($file);
    }

    /**
     * @return void
     */
    public function testDirectoryCreation(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/a/b/c/single.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/a/b/c');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/a/b');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/a');
    }

    /**
     * @return void
     */
    public function testBrokenFile(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Broken file',
            'Rendering rST failed'
        );
        $this->assertInLogs(
            'broken.rst:2: (WARNING/2)'
            . ' Bullet list ends without a blank line; unexpected unindent.'
        );
        $this->assertFileCreated('files/broken.html');
    }

    /**
     * @return void
     */
    public function testMissingFiles(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Missing attributes/tags',
            '"file" attribute or "fileset" subtag required'
        );
    }

    /**
     * @return void
     */
    public function testMultiple(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
        $this->assertFileCreated('files/two.html');
    }

    /**
     * @return void
     */
    public function testMultipleDir(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
        $this->assertFileCreated('files/two.html');
    }

    /**
     * @return void
     */
    public function testMultipleDirWildcard(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
    }

    /**
     * @return void
     */
    public function testMultipleMapper(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.my.html');
        $this->assertFileCreated('files/two.my.html');
    }

    /**
     * @return void
     */
    public function testNotMatchingMapper(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('No filename mapper found for "./files/single.rst"');

        $this->executeTarget(__FUNCTION__);
    }

    /**
     * @return void
     */
    public function testFilterChain(): void
    {
        $this->executeTarget(__FUNCTION__);
        $file = PHING_TEST_BASE . '/etc/tasks/ext/rst/files/filterchain.html';
        $this->assertFileExists($file);
        $cont = file_get_contents($file);
        $this->assertStringContainsString('This is a bar.', $cont);
        unlink($file);
    }

    /**
     * @return void
     */
    public function testCustomParameter(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists('files/single.html');
        $file = PHING_TEST_BASE . '/etc/tasks/ext/rst/files/single.html';
        $cont = file_get_contents($file);
        $this->assertStringContainsString('this is a custom css file', $cont);
        $this->assertStringContainsString('#FF8000', $cont);
        unlink($file);
    }
}
