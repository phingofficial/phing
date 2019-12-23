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
class GitGcTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitGcTaskTest.xml'
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
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('allParamsSet');
        $this->assertInLogs('git-gc: cleaning up "' . $repository . '" repository');
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
    public function testAutoParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $msg        = 'git-gc: cleaning up "' . $repository . '" repository';

        $this->executeTarget('autoParamSet');
        $this->assertInLogs($msg);
    }

    /**
     * @return void
     */
    public function testNoPruneParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $msg        = 'git-gc: cleaning up "' . $repository . '" repository';

        $this->executeTarget('nopruneParamSet');
        $this->assertInLogs($msg);
    }

    /**
     * @return void
     */
    public function testAggressiveParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $msg        = 'git-gc: cleaning up "' . $repository . '" repository';

        $this->executeTarget('aggressiveParamSet');
        $this->assertInLogs($msg);
    }

    /**
     * @return void
     */
    public function testPruneParameter(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $msg        = 'git-gc: cleaning up "' . $repository . '" repository';

        $this->executeTarget('pruneParamSet');
        $this->assertInLogs($msg);
    }
}
