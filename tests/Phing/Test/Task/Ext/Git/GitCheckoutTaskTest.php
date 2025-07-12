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
class GitCheckoutTaskTest extends BuildFileTest
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
        mkdir(PHING_TEST_BASE . '/tmp/git');

        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitCheckoutTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
    }

    public function testCheckoutExistingBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutExistingBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch co-branch set up to track');
        // @todo - actually make sure that Ebihara updates code to return (not
        // echo output from $command->execute()
        //$this->assertInLogs("Switched to branch 'test'");
        $this->assertInLogs('git-checkout output: '); // no output actually
    }

    public function testCheckoutNonExistingBranch(): void
    {
        $this->expectBuildExceptionContaining(
            'checkoutNonExistingBranch',
            'Checkout of non-existent repo is impossible',
            'Task execution failed'
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

    public function testNoBranchnameSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noBranchname',
            'Branchname is required',
            '"branchname" is required parameter'
        );
    }

    public function testCheckoutMerge(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutMerge');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining('git-branch output: Branch co-branch set up to track');
        $this->assertInLogs('git-branch output: Deleted branch master');
    }

    public function testCheckoutCreateBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutCreateBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertLogLineContaining(
            'git-checkout output: Branch co-create-branch set up to track'
        );
        $this->assertInLogs('git-branch output: Deleted branch co-create-branch');
    }

    public function testForceCheckoutCreateBranch(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('checkoutForceCreateBranch');
        $this->assertInLogs('git-checkout: checkout "' . $repository . '" repository');
        $this->assertInLogs('git-branch output: Deleted branch co-create-branch');
    }

    public function testForceCheckoutCreateBranchFailed(): void
    {
        $this->expectBuildExceptionContaining(
            'checkoutForceCreateBranchFailed',
            'Branch already exists',
            'Task execution failed.'
        );
    }
}
