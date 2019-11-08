<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanCommandBuilderTest extends TestCase
{

    /** @var PHPStanCommandBuilderFake */
    private $builder;

    protected function setUp(): void
    {
        $this->builder = new PHPStanCommandBuilderFake();
    }

    public function testItHandleBaseCommandParts(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('anyExecutable');
        $task->setCommand('anyCommand');

        $this->builder->build($task);

        $cmd = <<<CMD
Executing 'anyExecutable' with arguments:
'anyCommand'
The ' characters around the executable and arguments are not part of the command.
CMD;

        self::assertEquals($cmd, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }

    /**
     * @expectedException BuildException
     */
    public function testItFailsWhenExecutableNotSet(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('');

        $this->builder->build($task);
    }

    public function testItHandlesCommonOptions(): void
    {
        $task = new PHPStanTask();
        $task->setExecutable('anyExecutable');
        $task->setCommand('anyCommand');

        $task->setHelp(true);
        $task->setQuiet(true);
        $task->setVersion(true);
        $task->setANSI(true);
        $task->setNoANSI(true);
        $task->setNoInteraction(true);
        $task->setVerbose(true);

        $this->builder->build($task);

        $expectedCommand = <<<CMD
Executing 'anyExecutable' with arguments:
'anyCommand'
'--help'
'--quiet'
'--version'
'--ansi'
'--no-ansi'
'--no-interaction'
'--verbose'
The ' characters around the executable and arguments are not part of the command.
CMD;

        self::assertEquals($expectedCommand, str_replace("\r", '', $task->getCommandline()->describeCommand()));
    }
}
