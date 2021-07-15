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

namespace Phing\Test\Task\Optional;

use Phing\Test\Support\BuildFileTest;

/**
 * Tests the FileSync Task.
 *
 * @author  Anton <i.splinter@i.ua>
 *
 * @internal
 * @coversNothing
 */
class FileSyncTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/FileSyncTaskTest.xml'
        );
        $this->executeTarget('setup');
    }

    public function tearDown(): void
    {
        $this->executeTarget('clean');
    }

    public function testNoSourceSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noSourceDir',
            'No source set',
            '"sourcedir" attribute is missing'
        );
    }

    public function testNoDestinationSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noDestinationDir',
            'No destination set',
            '"destinationdir" attribute is missing'
        );
    }

    public function testNonexistentSource(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongSource',
            'Local source directory must be readable',
            'No such file or directory'
        );
    }

//    public function testLocalFileSync()
//    {
//        $this->executeTarget(__FUNCTION__);
//    }
//
//    public function testRemoteFileSync()
//    {
//        $this->executeTarget(__FUNCTION__);
//    }

    public function testRemoteToRemoteSync(): void
    {
        $this->expectBuildExceptionContaining(
            __FUNCTION__,
            'Either "sourcedir" or "destinationdir" must be local',
            'The source and destination cannot both be remote'
        );
    }
}
