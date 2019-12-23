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
 * @requires OS ^(?:(?!Win).)*$
 */
class GitInitTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitInitTaskTest.xml'
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
    public function testWrongRepository(): void
    {
        $this->expectBuildExceptionContaining(
            'wrongRepository',
            'Repository directory not readable',
            'You must specify readable directory as repository.'
        );
    }

    /**
     * @return void
     */
    public function testGitInit(): void
    {
        $repository  = PHING_TEST_BASE . '/tmp/git';
        $gitFilesDir = $repository . '/.git';
        $this->executeTarget('gitInit');

        $this->assertInLogs('git-init: initializing "' . $repository . '" repository');
        $this->assertDirectoryExists($repository);
        $this->assertDirectoryExists($gitFilesDir);
    }

    /**
     * @return void
     */
    public function testGitInitBare(): void
    {
        $repository = PHING_TEST_BASE . '/tmp/git';
        $this->executeTarget('gitInitBare');
        $this->assertInLogs('git-init: initializing (bare) "' . $repository . '" repository');
        $this->assertDirectoryExists($repository);
        $this->assertDirectoryExists($repository . '/branches');
        $this->assertDirectoryExists($repository . '/info');
        $this->assertDirectoryExists($repository . '/hooks');
        $this->assertDirectoryExists($repository . '/refs');
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
}
