<?php

declare(strict_types=1);

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

    /**
     * @depends testItRun
     */
    public function testExecutableCanBeSet(): void
    {
        $this->executeTarget("testExecutableChange");

        $expectedCommand = '/non/existing/path/to/phpstan'; // I hope
        $expectedCommand .= ' analyse';

        $this->assertExpectedCommandInLogs($expectedCommand);
    }

    /**
     * @depends testItRun
     * @expectedException BuildException
     * @expectedExceptionMessage unknown command
     */
    public function testTestInvalidCommandCausesBuildError(): void
    {
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

    private function assertExpectedCommandInLogs(string $expectedCommand): void
    {
        $this->assertInLogs('Executing: ' . $expectedCommand, Project::MSG_INFO);
    }
}
