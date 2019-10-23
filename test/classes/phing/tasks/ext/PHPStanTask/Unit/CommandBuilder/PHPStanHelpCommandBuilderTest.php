<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanHelpCommandBuilderTest extends TestCase
{
    /** @var PHPStanHelpCommandBuilder */
    private $builder;

    public function setUp(): void
    {
        $this->builder = new PHPStanHelpCommandBuilder();
    }

    public function testItHandlesCommandOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('phpstan');
        $task->setCommand('help');

        $task->setFormat('anyFormat');
        $task->setRaw(true);
        $task->setCommandName('anyCommand');

        $this->builder->build($task);

        $expectedCommand = <<<CMD
Executing 'phpstan' with arguments:
'help'
'--format=anyFormat'
'--raw'
'anyCommand'
The ' characters around the executable and arguments are not part of the command.
CMD;

        $this->assertEquals($expectedCommand, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }
}
