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

        $command = $this->builder->build($task);

        $this->assertEquals('anyExecutable anyCommand', $command);
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

        $command = $this->builder->build($task);

        $expectedCommand = 'anyExecutable anyCommand';
        $expectedCommand .= ' --help';
        $expectedCommand .= ' --quiet';
        $expectedCommand .= ' --version';
        $expectedCommand .= ' --ansi';
        $expectedCommand .= ' --no-ansi';
        $expectedCommand .= ' --no-interaction';
        $expectedCommand .= ' --verbose';

        $this->assertEquals($expectedCommand, $command);
    }
}
