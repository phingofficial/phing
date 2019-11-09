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
class GitFetchTaskTest extends BuildFileTest
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
            . '/etc/tasks/ext/git/GitFetchTaskTest.xml'
        );
    }

    public function tearDown(): void
    {
        $this->rmdir($this->uri);
    }

    public function testAllParamsSet()
    {
        $repository = $this->uri;
        $this->executeTarget('allParamsSet');
        $this->assertInLogs('git-fetch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-fetch output: '); // no output actually
    }

    public function testFetchAllRemotes()
    {
        $repository = $this->uri;
        $this->executeTarget('fetchAllRemotes');
        $this->assertInLogs('git-fetch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-fetch output: Fetching origin');
    }

    public function testNoRepositorySpecified()
    {
        $this->expectBuildExceptionContaining(
            'noRepository',
            'Repo dir is required',
            '"repository" is required parameter'
        );
    }

    public function testNoTargetSpecified()
    {
        $this->expectBuildExceptionContaining(
            'noTarget',
            'Target is required',
            'No remote repository specified'
        );
    }

    public function testRefspecSet()
    {
        $repository = $this->uri;
        $this->executeTarget('refspecSet');
        $this->assertInLogs('git-fetch: branch "' . $repository . '" repository');
        $this->assertInLogs('git-fetch output: ');
        $this->assertInLogs('Deleted branch refspec-branch');
    }
}
