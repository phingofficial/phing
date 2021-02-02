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

namespace Phing\Task\System;

use Phing\Exception\BuildException;
use Phing\Io\File;
use Phing\Support\BuildFileTest;

/**
 * Tests the Append / Concat Task
 *
 */
class AppendTaskTest extends BuildFileTest
{
    private $tempFile = 'concat.tmp';
    private $tempFile2 = 'concat.tmp.2';

    /**
     * Setup the test
     */
    public function setUp(): void
    {
        $this->configureProject(PHING_TEST_BASE . '/etc/tasks/system/AppendTest.xml');
    }

    public function tearDown(): void
    {
        $this->getProject()->executeTarget('cleanup');
    }

    public function test1()
    {
        $this->expectException(BuildException::class);

        $this->getProject()->executeTarget(__FUNCTION__);
    }

    public function test2()
    {
        $this->expectException(BuildException::class);

        $this->getProject()->executeTarget(__FUNCTION__);
    }

    public function test3()
    {
        $file = new File($this->getProject()->getBasedir(), $this->tempFile);
        if ($file->exists()) {
            $file->delete();
        }

        $this->executeTarget(__FUNCTION__);

        $this->assertTrue($file->exists());
    }

    public function test4()
    {
        $this->expectLog(__FUNCTION__, 'Hello, World!');
    }

    public function testConcatNoNewline()
    {
        $this->expectLog(__FUNCTION__, 'ab');
    }

    public function testPath()
    {
        $this->test3();

        $file = new File($this->getProject()->getBasedir(), $this->tempFile);
        $origSize = $file->length();

        $this->executeTarget("testPath");

        $file2 = new File($this->getProject()->getBasedir(), $this->tempFile2);
        $newSize = $file2->length();

        $this->assertEquals($origSize, $newSize);
    }

    public function testAppend()
    {
        $this->test3();

        $file = new File($this->getProject()->getBasedir(), $this->tempFile);
        $origSize = $file->length();

        $this->executeTarget("testAppend");

        $file2 = new File($this->getProject()->getBasedir(), $this->tempFile2);
        $newSize = $file2->length();

        $this->assertEquals($origSize * 2, $newSize);
    }

    public function testFilter()
    {
        $this->expectLog("testfilter", 'REPLACED');
    }

    public function testNoOverwrite()
    {
        $this->executeTarget("testnooverwrite");
        $file2 = new File($this->getProject()->getBasedir(), $this->tempFile2);
        $size = $file2->length();
        $this->assertEquals($size, 0);
    }

    public function testheaderfooter()
    {
        $this->test3();
        $this->expectLog("testheaderfooter", 'headerHello, World!footer');
    }

    public function testfileheader()
    {
        $this->test3();
        $this->expectLog("testfileheader", 'Hello, World!Hello, World!');
    }

    /**
     * Expect an exception when attempting to cat an file to itself
     */
    public function testsame()
    {
        $this->expectException(BuildException::class);

        $this->executeTarget("samefile");
    }

    public function testfilterinline()
    {
        $this->expectLogContaining('testfilterinline', 'REPLACED');
    }

    public function testfixlastline()
    {
        $this->executeTarget("testfixlastline");
        $this->assertStringContainsString(
            "end of line" . $this->getProject()->getProperty("line.separator") . "This has",
            file_get_contents($this->getProject()->getProperty("basedir") . 'concat.line4')
        );
    }

    public function testfixlastlineeol()
    {
        $this->executeTarget("testfixlastlineeol");
        $this->assertStringContainsString(
            "end of line\rThis has",
            file_get_contents($this->getProject()->getProperty("basedir") . 'concat.linecr')
        );
    }
}
