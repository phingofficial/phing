<?php
/*
 *
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

use org\bovigo\vfs\vfsStream;

/**
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @package phing.tasks.ext
 * @requires OS WIN32|WINNT
 */
class GitBranchTaskTest extends BuildFileTest
{
    private const DATA_PATH = 'root';

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $uri;

    public function setUp(): void
    {
        $structure = [
            'tmp' => [],
        ];

        vfsStream::setup(self::DATA_PATH, null, $structure);

        $this->uri = vfsStream::url(self::DATA_PATH . '/tmp/git');

        // set temp directory used by test cases
        mkdir($this->uri, 0777, true);

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitBranchTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir($this->uri);
    }

    public function testAllParamsSet()
    {
        $this->executeTarget('allParamsSet');
        $this->assertLogLineContaining(
            'git-branch output: Branch all-params-set set up to track remote branch master from origin'
        );
    }

    public function testNoRepositorySpecified()
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoBranchnameSpecified()
    {
        $this->expectBuildExceptionContaining(
            'noBranchname',
            'Branchname dir is required',
            '"branchname" is required parameter'
        );
    }

    public function testTrackParameter()
    {
        $repository = $this->uri;

        $this->executeTarget('trackParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch track-param-set set up to track local branch master');
    }

    public function testNoTrackParameter()
    {
        $repository = $this->uri;

        $this->executeTarget('noTrackParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: '); // no output actually
    }

    public function testSetUpstreamParameter()
    {
        $repository = $this->uri;

        if (version_compare(substr(trim(exec('git --version')), strlen('git version ')), '2.15.0', '<')) {
            $this->executeTarget('setUpstreamParamSet');
        } else {
            $this->executeTarget('setUpstreamToParamSet');
        }
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('Branch set-upstream-param-set set up to track local branch master'); // no output actually
    }

    public function testForceParameter()
    {
        $repository = $this->uri;

        $this->executeTarget('forceParamSet');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: '); // no output actually
    }

    public function testDeleteBranch()
    {
        $repository = $this->uri;

        $this->executeTarget('deleteBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        $this->assertLogLineContaining('Branch delete-branch-1 set up to track local branch master');
        $this->assertLogLineContaining('Branch delete-branch-2 set up to track local branch master');
        $this->assertInLogs('Deleted branch delete-branch-1');
        $this->assertInLogs('Deleted branch delete-branch-2');
    }

    public function testMoveBranch()
    {
        $repository = $this->uri;

        $this->executeTarget('moveBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        // try to delete new branch (thus understanding that rename worked)
        $this->assertInLogs('Deleted branch move-branch-2');
    }

    public function testForceMoveBranch()
    {
        $repository = $this->uri;

        $this->executeTarget('forceMoveBranch');
        $this->assertInLogs('git-branch: branch "' . $repository . '" repository');
        // try to delete new branch (thus understanding that rename worked)
        $this->assertInLogs('Deleted branch move-branch-2');
    }

    public function testForceMoveBranchNoNewbranch()
    {
        $this->expectBuildExceptionContaining(
            'forceMoveBranchNoNewbranch',
            'New branch name is required in branch move',
            '"newbranch" is required parameter'
        );
    }

    public function testMoveBranchNoNewbranch()
    {
        $this->expectBuildExceptionContaining(
            'moveBranchNoNewbranch',
            'New branch name is required in branch move',
            '"newbranch" is required parameter'
        );
    }
}
