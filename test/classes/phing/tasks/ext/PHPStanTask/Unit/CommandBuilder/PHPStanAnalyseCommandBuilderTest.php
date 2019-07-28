<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanAnalyseCommandBuilderTest extends TestCase
{

    /** @var PHPStanAnalyseCommandBuilder */
    private $builder;

    public function setUp(): void
    {
        $this->builder = new PHPStanAnalyseCommandBuilder();
    }

    public function testItHandlesCommandOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('phpstan');
        $task->setCommand('analyse');

        $task->setConfiguration('anyConfiguration');
        $task->setLevel('anyLevel');
        $task->setNoProgress(true);
        $task->setDebug(true);
        $task->setAutoloadFile('anyAutoloadFile');
        $task->setErrorFormat('anyErrorFormat');
        $task->setMemoryLimit('anyMemoryLimit');
        $task->setPaths('path1 path2');

        $command = $this->builder->build($task);

        $expectedCommand = 'phpstan analyse';
        $expectedCommand .= ' --configuration=anyConfiguration';
        $expectedCommand .= ' --level=anyLevel';
        $expectedCommand .= ' --no-progress';
        $expectedCommand .= ' --debug';
        $expectedCommand .= ' --autoload-file=anyAutoloadFile';
        $expectedCommand .= ' --error-format=anyErrorFormat';
        $expectedCommand .= ' --memory-limit=anyMemoryLimit';
        $expectedCommand .= ' path1 path2';

        $this->assertEquals($expectedCommand, $command);
    }
}
