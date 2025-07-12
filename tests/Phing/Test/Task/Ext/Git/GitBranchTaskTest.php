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
class GitBranchTaskTest extends BuildFileTest
{
    public function setUp(): void
    {
        if (! class_exists('VersionControl_Git')) {
            $this->markTestSkipped('The Git tasks depend on the pear/versioncontrol_git package being installed.');
        }
        if (is_readable(PHING_TEST_BASE . '/tmp/git')) {
            // make sure we purge previously created directory
            // if left-overs from previous run are found
            $this->rmdir(PHING_TEST_BASE . '/tmp/git');
        }
        // set temp directory used by test cases
        mkdir(PHING_TEST_BASE . '/tmp/git', 0777, true);

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitBranchTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
    }

    public function testAllParamsSet(): void
    {
        $this->executeTarget('allParamsSet');
        $this->assertLogLineContaining('git-branch output: Branch all-params-set set up to track');
    }

    public function testNoRepositorySpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoBranchnameSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noBranchname',
            'Branchname dir is required',
            '"branchname" is required parameter'
        );
    }

    public function testTrackParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('trackParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch track-param-set set up to track');
    }

    public function testNoTrackParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('noTrackParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: '); // no output actually
    }

    public function testSetUpstreamParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        if (version_compare(substr(trim(exec('git --version')), strlen('git version ')), '2.15.0', '<')) {
            $this->executeTarget('setUpstreamParamSet');
        } else {
            $this->executeTarget('setUpstreamToParamSet');
        }
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('Branch set-upstream-param-set set up to track'); // no output actually
    }

    public function testForceParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('forceParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: '); // no output actually
    }

    public function testDeleteBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('deleteBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('Branch delete-branch-1 set up to track');
        $this->assertLogLineContaining('Branch delete-branch-2 set up to track');
        $this->assertInLogs('Deleted branch delete-branch-1');
        $this->assertInLogs('Deleted branch delete-branch-2');
    }

    public function testMoveBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('moveBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        // try to delete new branch (thus understanding that rename worked)
        $this->assertInLogs('Deleted branch move-branch-2');
    }

    public function testForceMoveBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('forceMoveBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        // try to delete new branch (thus understanding that rename worked)
        $this->assertInLogs('Deleted branch move-branch-2');
    }

    public function testForceMoveBranchNoNewbranch(): void
    {
        $this->expectBuildExceptionContaining(
            'forceMoveBranchNoNewbranch',
            'New branch name is required in branch move',
            '"newbranch" is required parameter'
        );
    }

    public function testMoveBranchNoNewbranch(): void
    {
        $this->expectBuildExceptionContaining(
            'moveBranchNoNewbranch',
            'New branch name is required in branch move',
            '"newbranch" is required parameter'
        );
    }
}
