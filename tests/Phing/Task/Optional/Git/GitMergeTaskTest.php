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

namespace Phing\Test\Task\Optional\Git;

use Phing\Test\Support\BuildFileTest;

/**
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @requires OSFAMILY Linux
 *
 * @internal
 * @coversNothing
 */
class GitMergeTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (is_readable(PHING_TEST_BASE . '/tmp/git')) {
            // make sure we purge previously created directory
            // if left-overs from previous run are found
            $this->rmdir(PHING_TEST_BASE . '/tmp/git');
        }
        // set temp directory used by test cases
        mkdir(PHING_TEST_BASE . '/tmp/git');

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitMergeTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
    }

    public function testAllParamsSet(): void
    {
        $this->executeTarget('allParamsSet');
        $this->assertInLogs('git-merge: replaying "merge-test-1 merge-test-2" commits');
        $this->assertInLogs('git-merge output: Already up');
    }

    public function testNoCommitSet(): void
    {
        $this->executeTarget('noCommitSet');
        $this->assertInLogs('git-merge: replaying "6dbaf4508e75dcd426b5b974a67c462c70d46e1f" commits');
        $this->assertInLogs('git-merge output: Already up');
    }

    public function testRemoteSet(): void
    {
        $this->executeTarget('remoteSet');
        $this->assertInLogs('git-merge: replaying "6dbaf4508e75dcd426b5b974a67c462c70d46e1f" commits');
        $this->assertInLogs('git-merge output: Already up');
    }

    public function testFastForwardCommitSet(): void
    {
        $this->executeTarget('fastForwardCommitSet');
        $this->assertInLogs('git-merge command: LC_ALL=C && git merge --no-ff \'origin/master\'');
        $this->assertInLogs('git-merge: replaying "origin/master" commits');
        $this->assertInLogs('Merge remote-tracking branch \'origin/master\' into merge-test-1');
    }

    public function testNoRepositorySpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoRemotesSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noRemotes',
            'At least one commit is required',
            '"remote" is required parameter'
        );
    }

    public function testWrongStrategySet(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongStrategySet',
            'Wrong strategy passed',
            'Could not find merge strategy \'plain-wrong\''
        );
    }
}
