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
 * Tests the Delete Task.
 *
 * @author  Michiel Rook <mrook@php.net>
 *
 * @internal
 * @coversNothing
 */
class DeleteTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/system/DeleteTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testCopyDanglingSymlink(): void
    {
        $this->executeTarget('testDeleteDanglingSymlink');
        $this->assertInLogs('Deleting 1 files from');
    }

    public function testDeleteNonExistingDirectory(): void
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'does not exist or is not a directory');
    }

    public function testDeleteNonExistingFile(): void
    {
        $this->expectBuildExceptionContaining(__FUNCTION__, __FUNCTION__, 'Could not find file');
    }

    public function testDirset(): void
    {
        $tmpDir = $this->getProject()->getProperty('tmp.dir');
        $dir1 = $tmpDir . '/test-one';
        $dir2 = $tmpDir . '/test-two';
        $dir3 = $tmpDir . '/test-three';

        $this->executeTarget(__FUNCTION__);
        $this->assertDirectoryDoesNotExist($dir1);
        $this->assertDirectoryDoesNotExist($dir2);
        $this->assertDirectoryDoesNotExist($dir3);
    }
}
