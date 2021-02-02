<?php

namespace Phing\Task\Optional\Hg;

use Phing\Support\BuildFileTest;

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
class HgTagTaskTest extends BuildFileTest
{
    use HgTaskTestSkip;

    public function setUp(): void
    {
        mkdir(PHING_TEST_BASE . '/tmp/hgtest');
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/hg/HgTagTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . "/tmp/hgtest");
    }

    public function testRepoDoesntExist()
    {
        $this->expectBuildExceptionContaining(
            'wrongRepositoryDirDoesntExist',
            'wrongRepositoryDirDoesntExist',
            "Repository directory 'inconcievable-buttercup' does not exist."
        );
    }

    /*
    public function testTag()
    {
        $this->expectBuildExceptionContaining(
            "tag",
            "tag",
            "abort: cannot tag null revision"
        );
        $this->assertInLogs('Executing: tag --user \'test\' new-tag');
    }
    */

    public function testRevision()
    {
        $this->markTestAsSkippedWhenHgNotInstalled();

        $this->expectBuildExceptionContaining(
            "testRevision",
            "testRevision",
            "abort: unknown revision 'deadbeef'"
        );
        $this->assertInLogs(
            'Executing: tag --rev'
        );
    }
}
