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

        $this->builder->build($task);
        $expectedCommand = <<< CMD
Executing 'phpstan' with arguments:
'analyse'
'--configuration=anyConfiguration'
'--level=anyLevel'
'--no-progress'
'--debug'
'--autoload-file=anyAutoloadFile'
'--error-format=anyErrorFormat'
'--memory-limit=anyMemoryLimit'
'path1 path2'
The ' characters around the executable and arguments are not part of the command.
CMD;

        $this->assertEquals($expectedCommand, $task->getCommandline()->describeCommand());
    }
}
