<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanListCommandBuilderTest extends TestCase
{

    /** @var PHPStanListCommandBuilder */
    private $builder;

    public function setUp(): void
    {
        $this->builder = new PHPStanListCommandBuilder();
    }

    public function testItHandlesCommandOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('phpstan');
        $task->setCommand('list');

        $task->setFormat('anyFormat');
        $task->setRaw(true);
        $task->setNamespace('anyNamespace');

        $this->builder->build($task);

        $expectedCommand = <<<CMD
Executing 'phpstan' with arguments:
'list'
'--format=anyFormat'
'--raw'
'anyNamespace'
The ' characters around the executable and arguments are not part of the command.
CMD;

        $this->assertEquals($expectedCommand, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }
}
