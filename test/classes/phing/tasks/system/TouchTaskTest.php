<?php
use PHPUnit\Framework\Constraint\IsEqual;

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
        // Don't run the test on 32-bit systems
        if (PHP_INT_SIZE > 4) {
            $this->executeTarget(__FUNCTION__);
            $testFile = $this->getProject()->getProperty('tmp.dir') . '/millis-file';
            $this->assertFileExists($testFile);

            $this->assertEquals('December 31 1999 23:59:59', date("F d Y H:i:s", filemtime($testFile)));
        } else {
            $this->markTestSkipped('Test cannot run on 32-bit systems, epoch millis would have a max of ~25 days');
        }
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

    /**
     * test datetime attribute
     */
    public function testDatetime()
    {
        $this->executeTarget(__FUNCTION__);
        $testFile = $this->getProject()->getProperty('tmp.dir') . '/datetime-file';
        $this->assertFileExists($testFile);

        $this->assertEquals('December 31 1999 23:59:59', date("F d Y H:i:s", filemtime($testFile)));
    }

    /**
     * test datetime with improper datetime
     */
    public function testNotDateTime()
    {
        $this->expectBuildException(__FUNCTION__, 'when datetime has invalid value');
    }

    public function testNoFile()
    {
        $this->expectBuildException(__FUNCTION__, 'when no file specified');
    }

    public function testFileIsDirectory()
    {
        $this->expectBuildException(__FUNCTION__, 'when file specified is a directory');
    }

    public function testDatetimePreEpoch()
    {
        $this->expectBuildException(__FUNCTION__, 'when datetime is prior to January 1, 1970');
    }

    public function testReadOnlyFile()
    {
        $readOnlyFile = $this->getProject()->getProperty('tmp.dir') . '/readonly-file';
        if (file_exists($readOnlyFile)) {
            chmod($readOnlyFile, 0666); // ensure file is writable
        }
        $writeCnt = file_put_contents($readOnlyFile, 'TouchTaskTest file');
        if (false !== $writeCnt) {
            $this->getProject()->setProperty('readonly.file', $readOnlyFile);

            chmod($readOnlyFile, 0444);

            try {
                $this->executeTarget(__FUNCTION__);
                $this->fail('Should not be able to "touch" a read-only file');
            } catch (Exception $e) {
                // A BuildException is expected to be thrown
                $this->assertInstanceOf(BuildException::class, $e);
            } finally {
                chmod($readOnlyFile, 0666);
                unlink($readOnlyFile);
            }
        } else {
            $this->fail('Unable to create test file: ' . $readOnlyFile);
        }
    }

    public function testMillisNegative()
    {
        $this->expectBuildException(__FUNCTION__, 'when millis is negative');
    }

    public function testSecondsNegative()
    {
        $this->expectBuildException(__FUNCTION__, 'when seconds is negative');
    }

    public function testMillisSubSecond()
    {
        $this->expectBuildException(__FUNCTION__, 'when millis is less than a second');
    }

    public function testDefaultToNow()
    {
        $nowTime = time();

        $this->executeTarget(__FUNCTION__);
        $testFile = $this->getProject()->getProperty('tmp.dir') . '/default-now-file';
        $this->assertFileExists($testFile);

        /*
         * Assert that the timestamp is within 1 second of the time the test
         * started. Ideally it's exactly the same but we'll allow for minimal
         * drift to account for a lag between when we noted the time and when
         * the file was touched.
         */
        $this->assertThat(filemtime($testFile), new IsEqual($nowTime, 1), 'File timestamp not within 1 second of now');
    }
}
