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
class GitCheckoutTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitCheckoutTaskTest.xml'
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
    public function testCheckoutExistingBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutExistingBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch co-branch set up to track remote branch master from origin');
        // @todo - actually make sure that Ebihara updates code to return (not
        // echo output from $command->execute()
        //$this->assertInLogs("Switched to branch 'test'");
        $this->assertInLogs('git-checkout output: '); // no output actually
    }

    /**
     * @return void
     */
    public function testCheckoutNonExistingBranch(): void
    {
        $this->expectBuildExceptionContaining(
            'checkoutNonExistingBranch',
            'Checkout of non-existent repo is impossible',
            'Task execution failed'
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
            'Branchname is required',
            '"branchname" is required parameter'
        );
    }

    /**
     * @return void
     */
    public function testCheckoutMerge(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutMerge');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch co-branch set up to track remote branch master from origin');
        $this->assertInLogs('git-branch output: Deleted branch master');
    }

    /**
     * @return void
     */
    public function testCheckoutCreateBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutCreateBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining(
            'git-checkout output: Branch co-create-branch set up to track remote branch master from origin'
        );
        $this->assertInLogs('git-branch output: Deleted branch co-create-branch');
    }

    /**
     * @return void
     */
    public function testForceCheckoutCreateBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutForceCreateBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: Deleted branch co-create-branch');
    }

    /**
     * @return void
     */
    public function testForceCheckoutCreateBranchFailed(): void
    {
        $this->expectBuildExceptionContaining(
            'checkoutForceCreateBranchFailed',
            'Branch already exists',
            'Task execution failed.'
        );
    }
}
