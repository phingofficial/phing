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
class GitPushTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitPushTaskTest.xml'
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
        $this->assertInLogs('git-push: pushing to origin master:foobranch');
        $this->assertInLogs('git-push: complete');
    }

    /**
     * @return void
     */
    public function testAllReposSet(): void
    {
        $this->executeTarget('allReposSet');
        $this->assertInLogs('git-push: push to all refs');
        $this->assertInLogs('git-push: complete');
    }

    /**
     * @return void
     */
    public function testTagsSet(): void
    {
        $this->executeTarget('tagsSet');
        $this->assertInLogs('git-push: pushing to origin master:foobranch');
        $this->assertInLogs('git-push: complete');
    }

    /**
     * @return void
     */
    public function testDeleteSet(): void
    {
        $this->executeTarget('deleteSet');
        $this->assertInLogs('git-push: pushing to origin master:newbranch');
        $this->assertInLogs('git-push: branch delete requested');
        $this->assertInLogs('git-push: complete');
    }

    /**
     * @return void
     */
    public function testMirrorSet(): void
    {
        $this->executeTarget('mirrorSet');
        $this->assertInLogs('git-push: mirror all refs');
        $this->assertInLogs('git-push: complete');
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
    public function testWrongRepo(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongRepo',
            'Repo dir is wrong',
            'You must specify readable directory as repository.'
        );
    }

    /**
     * @return void
     */
    public function testNoDestinationSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noDestination',
            'No source set',
            'At least one destination must be provided'
        );
    }
}
