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

/**
 * Tests the Touch Task
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.tasks.system
 */
class TouchTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . "/etc/tasks/system/TouchTaskTest.xml"
        );
        $this->executeTarget("setup");
    }

    public function tearDown(): void
    {
        $this->executeTarget("clean");
    }

    public function testSimpleTouch()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . "/etc/tasks/system/tmp/simple-file"
        );
    }

    public function testMkdirs()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . "/etc/tasks/system/tmp/this/is/a/test/file"
        );
    }

    public function testMkdirsFails()
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('Error creating new file');

        $this->executeTarget(__FUNCTION__);

        $this->assertFileDoesNotExist(
            PHING_TEST_BASE
            . "/etc/tasks/system/tmp/this/is/a/test/file"
        );
    }

    public function testFilelist()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . "/etc/tasks/system/tmp/simple-file"
        );
    }

    public function testFileset()
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists(
            PHING_TEST_BASE
            . "/etc/tasks/system/tmp/simple-file"
        );
    }

    public function testMappedFileset()
    {
        $this->executeTarget(__FUNCTION__);
        $tmpDir = $this->getProject()->getProperty('tmp.dir');
        $this->assertFileExists($tmpDir . '/touchtest');
        $this->assertFileExists($tmpDir . '/touchtestfoo');
        $this->assertFileExists($tmpDir . '/touchtestbar');
    }

    /**
     * test the mapped file list
     */
    public function testMappedFilelist()
    {
        $this->executeTarget(__FUNCTION__);
        $tmpDir = $this->getProject()->getProperty('tmp.dir');
        $this->assertFileExists($tmpDir . '/touchtest');
    }

    /**
     * test millis attribute
     */
    public function testMillis()
    {
        $this->executeTarget(__FUNCTION__);
        $testFile = $this->getProject()->getProperty('tmp.dir') . '/millis-file';
        $this->assertFileExists($testFile);

        $this->assertEquals('December 31 1999 23:59:59', date("F d Y H:i:s", filemtime($testFile)));
    }

    /**
     * test seconds attribute
     */
    public function testSeconds()
    {
        $this->executeTarget(__FUNCTION__);
        $testFile = $this->getProject()->getProperty('tmp.dir') . '/seconds-file';
        $this->assertFileExists($testFile);

        $this->assertEquals('December 31 1999 23:59:59', date("F d Y H:i:s", filemtime($testFile)));
    }
}
