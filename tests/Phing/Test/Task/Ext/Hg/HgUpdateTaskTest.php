<?php

namespace Phing\Test\Task\Ext\Hg;

use Phing\Test\Support\BuildFileTest;

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
 * @internal
 */
class HgUpdateTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp(): void
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgUpdateTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/hgtest');
    }

    public function testWrongRepositoryDirDoesntExist(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongRepositoryDirDoesntExist',
            'repository directory does not exist',
            "Repository directory 'inconcievable-buttercup' does not exist."
        );
    }

    public function testWrongRepository(): void
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'wrong repository',
            'abort'
        );
        $this->assertInLogs('Executing: hg update');
    }
}
