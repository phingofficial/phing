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

        $command = $this->builder->build($task);

        $expectedCommand = 'phpstan list';
        $expectedCommand .= ' --format=anyFormat';
        $expectedCommand .= ' --raw';
        $expectedCommand .= ' anyNamespace';

        $this->assertEquals($expectedCommand, $command);
    }
}
