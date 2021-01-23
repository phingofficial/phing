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

use Phing\Exception\BuildException;
use Phing\Support\BuildFileTest;

/**
 * Unit test for reStructuredText rendering task.
 *
 * PHP version 5
 *
 * @category   Tasks
 * @package    phing.tasks.ext
 * @author     Christian Weiske <cweiske@cweiske.de>
 * @license    LGPL v3 or later http://www.gnu.org/licenses/lgpl.html
 * @link       http://www.phing.info/
 */
class RSTTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        //needed for PEAR's System class
        error_reporting(error_reporting() & ~E_STRICT & ~E_DEPRECATED);

        chdir(PHING_TEST_BASE . '/etc/tasks/ext/rst');
        $this->configureProject(
            PHING_TEST_BASE . '/etc/tasks/ext/rst/build.xml'
        );
    }

    public function tearDown(): void
    {
        // remove excess file if the test failed
        @unlink(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/single.html');
    }

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
    protected function assertFileCreated($file)
    {
        $this->assertFileExists(
            PHING_TEST_BASE . '/etc/tasks/ext/rst/' . $file,
            $file . ' has not been created'
        );
        unlink(PHING_TEST_BASE . '/etc/tasks/ext/rst/' . $file);
    }

    /**
     * @requires function ReflectionMethod::setAccessible
     */
    public function testGetToolPathFail()
    {
        $rt = new RSTTask();
        $ref = new ReflectionClass($rt);
        $method = $ref->getMethod('getToolPath');
        $method->setAccessible(true);

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('"rst2doesnotexist" not found. Install python-docutils.');

        $method->invoke($rt, 'doesnotexist');
    }

    /**
     * Get the tool path previously set with setToolpath()
     *
     * @requires function ReflectionMethod::setAccessible
     */
    public function testGetToolPathCustom()
    {
        $rt = new RSTTask();
        $rt->setToolpath('true'); //mostly /bin/true on unix
        $ref = new ReflectionClass($rt);
        $method = $ref->getMethod('getToolPath');
        $method->setAccessible(true);
        $this->assertStringContainsString('/true', $method->invoke($rt, 'foo'));
    }

    public function testSetToolpathNotExisting()
    {
        $rt = new RSTTask();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Tool does not exist. Path:');

        $rt->setToolpath('doesnotandwillneverexist');
    }

    public function testSetToolpathNonExecutable()
    {
        $rt = new RSTTask();

        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Tool not executable. Path:');

        $rt->setToolpath(__FILE__);
    }

    /**
     * @throws ReflectionException
     * @requires function ReflectionMethod::setAccessible
     */
    public function testGetToolPathHtmlFormat()
    {
        $rt = new RSTTask();
        $ref = new ReflectionClass($rt);
        $method = $ref->getMethod('getToolPath');
        $method->setAccessible(true);
        $this->assertStringContainsString('rst2html', $method->invoke($rt, 'html'));
    }

    public function testSingleFileParameterFile()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
    }

    public function testSingleFileParameterFileNoExt()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single-no-ext.html');
    }

    public function testSingleFileParameterFileFormat()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.3');
    }

    public function testSingleFileInvalidParameterFormat()
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Invalid parameter',
            'Invalid output format "foo", allowed are'
        );
    }

    public function testSingleFileParameterFileFormatDestination()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single-destination.html');
    }

    public function testParameterDestinationAsDirectory()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/subdir/files/single.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir/files');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir');
    }

    public function testParameterDestinationDirectoryWithFileset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/subdir/files/single.html');
        $this->assertFileCreated('files/subdir/files/two.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir/files');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir');
    }

    public function testParameterDestinationDirectoryWithFilesetDot()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/subdir/files/single.html');
        $this->assertFileCreated('files/subdir/files/two.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir/files');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/subdir');
    }

    public function testParameterUptodate()
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

    public function testDirectoryCreation()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/a/b/c/single.html');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/a/b/c');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/a/b');
        rmdir(PHING_TEST_BASE . '/etc/tasks/ext/rst/files/a');
    }

    public function testBrokenFile()
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

    public function testMissingFiles()
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Missing attributes/tags',
            '"file" attribute or "fileset" subtag required'
        );
    }

    public function testMultiple()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
        $this->assertFileCreated('files/two.html');
    }

    public function testMultipleDir()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
        $this->assertFileCreated('files/two.html');
    }

    public function testMultipleDirWildcard()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.html');
    }


    public function testMultipleMapper()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileCreated('files/single.my.html');
        $this->assertFileCreated('files/two.my.html');
    }

    public function testNotMatchingMapper()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('No filename mapper found for "./files/single.rst"');

        $this->executeTarget(__FUNCTION__);
    }


    public function testFilterChain()
    {
        $this->executeTarget(__FUNCTION__);
        $file = PHING_TEST_BASE . '/etc/tasks/ext/rst/files/filterchain.html';
        $this->assertFileExists($file);
        $cont = file_get_contents($file);
        $this->assertStringContainsString('This is a bar.', $cont);
        unlink($file);
    }


    public function testCustomParameter()
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
