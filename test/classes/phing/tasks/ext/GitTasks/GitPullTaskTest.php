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
class GitPullTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitPullTaskTest.xml'
        );
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->rmdir(PHING_TEST_BASE . '/tmp/git');
    }

    /**
     * @return void
     */
    public function testAllParamsSet(): void
    {
        $this->markTestIncomplete();

        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('allParamsSet');
        $this->assertInLogs('git-pull: pulling from origin foobranch');
        $this->assertInLogs('git-pull: complete');
        $this->assertInLogs('git-pull output: Updating 6dbaf45..6ad2ea3');
        // make sure that foofile from foobranch made it to master
        $this->assertTrue(is_readable($repository . '/foofile'));
    }

    /**
     * @return void
     */
    public function testAllParamsSetRebase(): void
    {
        $this->markTestIncomplete();

        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('allParamsSetRebase');
        $this->assertInLogs('git-pull: pulling from origin foobranch');
        $this->assertInLogs('git-pull: complete');
        $this->assertInLogs('git-pull output: First, rewinding head to replay your work on top of it...');
        $this->assertInLogs('Fast-forwarded master to 6ad2ea37a26ce3534073e89043f890c054fddb20.');
        // make sure that foofile from foobranch made it to master
        $this->assertTrue(is_readable($repository . '/foofile'));
    }

    /**
     * @return void
     */
    public function testAllReposSet(): void
    {
        $this->executeTarget('allReposSet');
        $this->assertInLogs('git-pull: fetching from all remotes');
        $this->assertInLogs('git-pull: complete');
    }

    /**
     * @return void
     */
    public function testTagsSet(): void
    {
        $this->markTestIncomplete();

        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('tagsSet');
        $this->assertInLogs('git-pull: pulling from origin foobranch');
        $this->assertInLogs('git-pull: complete');
        $this->assertInLogs('git-pull output: Updating 6dbaf45..6ad2ea3');
        // make sure that foofile from foobranch made it to master
        $this->assertTrue(is_readable($repository . '/foofile'));
    }

    /**
     * @return void
     */
    public function testAppendSet(): void
    {
        $this->executeTarget('appendSet');
        $this->assertInLogs('git-pull: fetching from all remotes');
        $this->assertInLogs('git-pull: complete');
    }

    /**
     * @return void
     */
    public function testNoTagsSet(): void
    {
        $this->markTestIncomplete();

        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('noTagsSet');
        $this->assertInLogs('git-pull: pulling from origin foobranch');
        $this->assertInLogs('git-pull: complete');
        $this->assertInLogs('git-pull output: Updating 6dbaf45..6ad2ea3');
        // make sure that foofile from foobranch made it to master
        $this->assertTrue(is_readable($repository . '/foofile'));
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
    public function testNoSourceSpecified(): void
    {
        $this->expectBuildExceptionContaining(
            'noSource',
            'At least one source must be provided',
            'No source repository specified'
        );
    }

    /**
     * @return void
     */
    public function testWrongStrategySet(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongStrategySet',
            'Wrong strategy passed',
            'Could not find merge strategy \'plain-wrong\''
        );
    }
}
