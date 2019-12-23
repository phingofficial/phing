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

declare(strict_types=1);

/**
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext
 * @requires OS WIN32|WINNT
 */
class GitBranchTaskTest extends BuildFileTest
{
    /**
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    protected function setUp(): void
    {
        // set temp directory used by test cases
        @mkdir(PHING_TEST_BASE . '/tmp/git', 0777, true);

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitBranchTaskTest.xml'
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
        $this->rmdir(PHING_TEST_BASE . '/tmp/repo');
    }

    /**
     * @return void
     */
    public function testAllParamsSet(): void
    {
        $this->executeTarget('allParamsSet');
        $this->assertLogLineContaining(
            'git-branch output: Branch all-params-set set up to track remote branch master from origin'
        );
    }

    /**
     * @return void
     */
    public function testNoRepositorySpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    /**
     * @return void
     */
    public function testNoBranchnameSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noBranchname',
            'Branchname dir is required',
            '"branchname" is required parameter'
        );
    }

    /**
     * @return void
     */
    public function testTrackParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('trackParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch track-param-set set up to track local branch master');
    }

    /**
     * @return void
     */
    public function testNoTrackParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('noTrackParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: '); // no output actually
    }

    /**
     * @return void
     */
    public function testSetUpstreamParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        if (version_compare(substr(trim((string) exec('git --version')), strlen('git version ')), '2.15.0', '<')) {
            $this->executeTarget('setUpstreamParamSet');
        } else {
            $this->executeTarget('setUpstreamToParamSet');
        }
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('Branch set-upstream-param-set set up to track local branch master'); // no output actually
    }

    /**
     * @return void
     */
    public function testForceParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('forceParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: '); // no output actually
    }

    /**
     * @return void
     */
    public function testDeleteBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('deleteBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('Branch delete-branch-1 set up to track local branch master');
        $this->assertLogLineContaining('Branch delete-branch-2 set up to track local branch master');
        $this->assertInLogs('Deleted branch delete-branch-1');
        $this->assertInLogs('Deleted branch delete-branch-2');
    }

    /**
     * @return void
     */
    public function testMoveBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('moveBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        // try to delete new branch (thus understanding that rename worked)
        $this->assertInLogs('Deleted branch move-branch-2');
    }

    /**
     * @return void
     */
    public function testForceMoveBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';

        $this->executeTarget('forceMoveBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        // try to delete new branch (thus understanding that rename worked)
        $this->assertInLogs('Deleted branch move-branch-2');
    }

    /**
     * @return void
     */
    public function testForceMoveBranchNoNewbranch(): void
    {
        $this->expectBuildExceptionContaining(
            'forceMoveBranchNoNewbranch',
            'New branch name is required in branch move',
            '"newbranch" is required parameter'
        );
    }

    /**
     * @return void
     */
    public function testMoveBranchNoNewbranch(): void
    {
        $this->expectBuildExceptionContaining(
            'moveBranchNoNewbranch',
            'New branch name is required in branch move',
            '"newbranch" is required parameter'
        );
    }
}
