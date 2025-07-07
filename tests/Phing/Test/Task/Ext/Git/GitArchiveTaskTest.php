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

namespace Phing\Test\Task\Ext\Git;

use Phing\Project;
use Phing\Test\Support\BuildFileTest;

/**
 * @author Siad Ardroumli <siad.ardroumli@gmail.com>
 * @requires OSFAMILY Linux
 *
 * @internal
 */
class GitArchiveTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (! class_exists('VersionControl_Git')) {
            $this->markTestSkipped('The Git tasks depend on the pear/versioncontrol_git package being installed.');
        }
        // set temp directory used by test cases
        mkdir(PHING_TEST_BASE . '/tmp/git');

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitArchiveTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
    }

    public function testGitArchive(): void
    {
        $this->executeTarget('gitArchive');
        $this->assertInLogs('git-archive:');
        $this->assertInLogs('repository (HEAD)', Project::MSG_DEBUG);
        $this->assertFileExists($this->getProject()->getProperty('tmp.dir.resolved') . '/output.zip');
    }

    public function testWrongRepository(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'Repository not readable',
            'You must specify readable directory as repository'
        );
    }

    public function testNoRepositorySpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoTreeishSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noTreeish',
            'Treeish is required',
            '"treeish" is required parameter'
        );
    }
}
