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

use Phing\Test\Support\BuildFileTest;

/**
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @requires OSFAMILY Linux
 *
 * @internal
 */
class GitCloneTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        // set temp directory used by test cases
        mkdir(PHING_TEST_BASE . '/tmp/git');

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitCloneTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
    }

    public function testWrongRepository(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'Repository not readable',
            'The remote end hung up unexpectedly'
        );
    }

    public function testGitClone(): void
    {
        $bundle = PHING_TEST_BASE . '/etc/tasks/ext/git/phing-tests.git';
        $repository = PHING_TEST_BASE . '/tmp/git';
        $gitFilesDir = $repository . '/.git';
        $this->executeTarget('gitClone');

        $this->assertInLogs('git-clone: cloning "' . $bundle . '" repository to "' . $repository . '" directory');
        $this->assertDirectoryExists($repository);
        $this->assertDirectoryExists($gitFilesDir);
        // test that file is actully cloned
        $this->assertIsReadable($repository . '/README');
    }

    public function testGitCloneBare(): void
    {
        $bundle = PHING_TEST_BASE . '/etc/tasks/ext/git/phing-tests.git';
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('gitCloneBare');
        $this->assertInLogs(
            'git-clone: cloning (bare) "' . $bundle . '" repository to "' . $repository . '" directory'
        );
        $this->assertDirectoryExists($repository);
        $this->assertDirectoryExists($repository . '/info');
        $this->assertDirectoryExists($repository . '/hooks');
        $this->assertDirectoryExists($repository . '/refs');
    }

    public function testNoRepositorySpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoTargetPathSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noTargetPath',
            'Target path is required',
            '"targetPath" is required parameter'
        );
    }
}
