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
class GitMergeTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitMergeTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir($this->uri);
    }

    public function testAllParamsSet()
    {
        $this->executeTarget('allParamsSet');
        $this->assertInLogs('git-merge: replaying "merge-test-1 merge-test-2" commits');
        $this->assertInLogs('git-merge output: Already up');
    }

    public function testNoCommitSet()
    {
        $this->executeTarget('noCommitSet');
        $this->assertInLogs('git-merge: replaying "6dbaf4508e75dcd426b5b974a67c462c70d46e1f" commits');
        $this->assertInLogs('git-merge output: Already up');
    }

    public function testRemoteSet()
    {
        $this->executeTarget('remoteSet');
        $this->assertInLogs('git-merge: replaying "6dbaf4508e75dcd426b5b974a67c462c70d46e1f" commits');
        $this->assertInLogs('git-merge output: Already up');
    }

    public function testFastForwardCommitSet()
    {
        $this->executeTarget('fastForwardCommitSet');
        $this->assertInLogs('git-merge command: LC_ALL=C && git merge --no-ff \'origin/master\'');
        $this->assertInLogs('git-merge: replaying "origin/master" commits');
        $this->assertInLogs('Merge remote-tracking branch \'origin/master\' into merge-test-1');
    }

    public function testNoRepositorySpecified()
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoRemotesSpecified()
    {
        $this->expectBuildExceptionContaining(
            'noRemotes',
            'At least one commit is required',
            '"remote" is required parameter'
        );
    }

    public function testWrongStrategySet()
    {
        $this->expectBuildExceptionContaining(
            'wrongStrategySet',
            'Wrong strategy passed',
            'Could not find merge strategy \'plain-wrong\''
        );
    }
}
