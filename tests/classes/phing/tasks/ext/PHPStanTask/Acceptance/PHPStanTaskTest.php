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

use Phing\Exception\BuildException;
use Phing\Project;

class PHPStanTaskTest extends BuildFileTest
{

    private const PHPSTAN_TEST_BASE = PHING_TEST_BASE . '/etc/tasks/ext/phpstan/';

    public function setUp(): void
    {
        $this->configureProject(self::PHPSTAN_TEST_BASE . "/PHPStanTaskTest.xml");
    }

    public function testItRun(): void
    {
        $this->executeTarget("testRun");

        $expectedCommand = 'phpstan analyse';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }

    public function testItRunWithFileset(): void
    {
        $this->executeTarget("testRunFileset");

        $this->assertExpectedCommandInLogs('phpstan analyse');
    }

    private function assertExpectedCommandInLogs(string $expectedCommand): void
    {
        $this->assertInLogs('Executing command: ' . $expectedCommand, Project::MSG_INFO);
    }

    /**
     * @depends testItRun
     */
    public function testExecutableCanBeSet(): void
    {
        $this->executeTarget("testExecutableChange");

        $expectedCommand = str_replace(
            '/',
            DIRECTORY_SEPARATOR,
            '/non/existing/path/to/phpstan'
        );
        $expectedCommand .= ' analyse';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }

    /**
     * @depends testItRun
     */
    public function testTestInvalidCommandCausesBuildError(): void
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage('unknown command');
        $this->executeTarget("testInvalidCommand");
    }

    /**
     * @depends testItRun
     */
    public function testAnalyseOptionsCanBeSet(): void
    {
        $this->executeTarget("testAnalyseOptions");

        $expectedCommand = 'phpstan analyse';
        $expectedCommand .= ' --configuration=anyConfiguration';
        $expectedCommand .= ' --level=anyLevel';
        $expectedCommand .= ' --no-progress';
        $expectedCommand .= ' --debug';
        $expectedCommand .= ' --autoload-file=anyAutoloadFile';
        $expectedCommand .= ' --error-format=anyErrorFormat';
        $expectedCommand .= ' --memory-limit=anyMemoryLimit';
        $expectedCommand .= ' path1 path2';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }

    /**
     * @depends testItRun
     */
    public function testHelpOptionsCanBeSet(): void
    {
        $this->executeTarget("testHelpOptions");

        $expectedCommand = 'phpstan help';
        $expectedCommand .= ' --format=anyFormat';
        $expectedCommand .= ' --raw';
        $expectedCommand .= ' anyCommand';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }

    /**
     * @depends testItRun
     */
    public function testListOptionsCanBeSet(): void
    {
        $this->executeTarget("testListOptions");

        $expectedCommand = 'phpstan list';
        $expectedCommand .= ' --format=anyFormat';
        $expectedCommand .= ' --raw';
        $expectedCommand .= ' anyNamespace';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }

    /**
     * @depends testItRun
     */
    public function testCommonOptionsCanBeSet(): void
    {
        $this->executeTarget("testCommonOptions");

        $expectedCommand = 'phpstan analyse';
        $expectedCommand .= ' --help';
        $expectedCommand .= ' --quiet';
        $expectedCommand .= ' --version';
        $expectedCommand .= ' --ansi';
        $expectedCommand .= ' --no-ansi';
        $expectedCommand .= ' --no-interaction';
        $expectedCommand .= ' --verbose';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }
}
