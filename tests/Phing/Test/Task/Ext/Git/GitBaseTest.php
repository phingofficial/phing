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

use Phing\Task\Ext\Git\GitBaseTask;
use Phing\Test\Support\BuildFileTest;

/**
 * @author Victor Farazdagi <simple.square@gmail.com>
 * @requires OSFAMILY Linux
 *
 * @internal
 */
class GitBaseTest extends BuildFileTest
{
    protected $mock;

    public function setUp(): void
    {
        if (! class_exists('VersionControl_Git')) {
            $this->markTestSkipped('The Git tasks depend on the pear/versioncontrol_git package being installed.');
        }
        $this->configureProject(
            PHING_TEST_BASE
            . '/etc/tasks/ext/git/GitBaseTest.xml'
        );
        $this->mock = $this->getMockForAbstractClass(GitBaseTask::class);
    }

    public function testInitialization(): void
    {
        $this->assertInstanceOf(GitBaseTask::class, $this->mock);
    }

    /**
     * @todo - make sure that required arguments are checked
     */
    public function testArguments(): void
    {
        $this->markTestIncomplete('needs investigation');
    }

    public function testMutators(): void
    {
        // gitPath
        $gitPath = $this->mock->getGitPath();
        $this->mock->setGitPath('my-new-path');
        $this->assertEquals('my-new-path', $this->mock->getGitPath());
        $this->mock->setGitPath($gitPath);

        // repository
        $repository = $this->mock->getRepository();
        $this->mock->setRepository('/tmp');
        $this->assertEquals('/tmp', $this->mock->getRepository());
        $this->mock->setRepository($repository);
    }
}
