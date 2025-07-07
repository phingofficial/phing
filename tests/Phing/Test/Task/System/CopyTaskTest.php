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

namespace Phing\Test\Task\System;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the Copy Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @internal
 */
class CopyTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/system/CopyTask/CopyTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testCopyDanglingSymlink(): void
    {
        $this->executeTarget('testCopyDanglingSymlink');
        $this->assertInLogs('Copying 1 file to');
    }

    /**
     * Test for {@link http://www.phing.info/trac/ticket/981}
     * FileUtil::copyFile(): preserveLastModified causes
     * empty symlink target file.
     */
    public function testCopySymlinkPreserveLastModifiedShouldCopyTarget(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Copying 2 files to');
        $this->assertGreaterThan(0, $this->project->getProperty('test.filesize'));
    }

    /**
     * Regression test for ticket {@link http://www.phing.info/trac/ticket/229}
     * - CopyTask should accept filelist subelement.
     */
    public function testCopyFileList(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Copying 2 files to');
    }

    /**
     * - CopyTask should accept dirset subelement.
     */
    public function testCopyDirSet(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Copying 2 files to');
    }

    /**
     * Regression test for ticket {@link https://github.com/phingofficial/phing/issues/562}
     * - Error overwriting symlinks on copy or move.
     */
    public function testOverwriteExistingSymlink(): void
    {
        $this->executeTarget(__FUNCTION__);
        $this->assertInLogs('Copying 1 file to');
        $this->assertEquals('tmp/target-a', readlink(PHING_TEST_BASE . '/etc/tasks/system/CopyTask/tmp/link-b'));
    }

    public function testGranularity(): void
    {
        $this->expectLogContaining(__FUNCTION__, 'Test omitted, Test is up to date');
    }

    public function testFilesetFiles(): void
    {
        $destinationDir = PHING_TEST_BASE . '/etc/tasks/system/CopyTask/tmp/destination';
        $this->assertDirectoryDoesNotExist($destinationDir);
        $this->executeTarget(__FUNCTION__);
        $this->assertFileExists("$destinationDir/Foo/Foo.php");
        $this->assertFileExists("$destinationDir/Bar/Bar.php");
        $this->assertFileExists("$destinationDir/Baz/Baz.php");
        $this->assertFileExists("$destinationDir/Qux/Qux.php");
        $this->assertFileDoesNotExist("$destinationDir/Foo/FooTest.php");
        $this->assertFileDoesNotExist("$destinationDir/Bar/BarTest.php");
        $this->assertFileDoesNotExist("$destinationDir/Baz/BazTest.php");
        $this->assertFileDoesNotExist("$destinationDir/Qux/QuxTest.php");
    }
}
