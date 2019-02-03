<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanHelpCommandBuilderTest extends TestCase
{
    /** @var PHPStanHelpCommandBuilder */
    private $builder;

    public function setUp(): void    {
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

        $command = $this->builder->build($task);

        $expectedCommand = 'phpstan help';
        $expectedCommand .= ' --format=anyFormat';
        $expectedCommand .= ' --raw';
        $expectedCommand .= ' anyCommand';

        $this->assertEquals($expectedCommand, $command);
    }
}
