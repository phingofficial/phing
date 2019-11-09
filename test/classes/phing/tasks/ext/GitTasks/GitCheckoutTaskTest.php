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
class GitCheckoutTaskTest extends BuildFileTest
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
        mkdir($this->uri);

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitCheckoutTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir($this->uri);
    }

    public function testCheckoutExistingBranch()
    {
        $repository = $this->uri;
        $this->executeTarget('checkoutExistingBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch co-branch set up to track remote branch master from origin');
        // @todo - actually make sure that Ebihara updates code to return (not
        // echo output from $command->execute()
        //$this->assertInLogs("Switched to branch 'test'");
        $this->assertInLogs('git-checkout output: '); // no output actually
    }

    public function testCheckoutNonExistingBranch()
    {
        $this->expectBuildExceptionContaining(
            'checkoutNonExistingBranch',
            'Checkout of non-existent repo is impossible',
            'Task execution failed'
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
            'Branchname is required',
            '"branchname" is required parameter'
        );
    }

    public function testCheckoutMerge()
    {
        $repository = $this->uri;
        $this->executeTarget('checkoutMerge');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch co-branch set up to track remote branch master from origin');
        $this->assertInLogs('git-branch output: Deleted branch master');
    }

    public function testCheckoutCreateBranch()
    {
        $repository = $this->uri;
        $this->executeTarget('checkoutCreateBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining(
            'git-checkout output: Branch co-create-branch set up to track remote branch master from origin'
        );
        $this->assertInLogs('git-branch output: Deleted branch co-create-branch');
    }

    public function testForceCheckoutCreateBranch()
    {
        $repository = $this->uri;
        $this->executeTarget('checkoutForceCreateBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: Deleted branch co-create-branch');
    }

    public function testForceCheckoutCreateBranchFailed()
    {
        $this->expectBuildExceptionContaining(
            'checkoutForceCreateBranchFailed',
            'Branch already exists',
            'Task execution failed.'
        );
    }
}
