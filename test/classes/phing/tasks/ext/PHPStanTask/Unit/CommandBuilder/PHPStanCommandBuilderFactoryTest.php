<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanCommandBuilderFactoryTest extends TestCase
{

    /** @var PHPStanCommandBuilderFactory */
    private $factory;

    public function setUp()
    {
        $this->factory = new PHPStanCommandBuilderFactory();
    }

    public function testItCanCreateAnalyseCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('analyse');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanAnalyseCommandBuilder::class, $builder);
    }

    public function testItCanCreateAnalyzeCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('analyze');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanAnalyseCommandBuilder::class, $builder);
    }

    public function testItCanCreateListCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('list');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanListCommandBuilder::class, $builder);
    }

    public function testItCanCreateHelpCommandBuilder(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('help');

        $builder = $this->factory->createBuilder($task);

        $this->assertInstanceOf(PHPStanHelpCommandBuilder::class, $builder);
    }

    /**
     * @expectedException BuildException
     */
    public function testItThrowsExceptionWhenCommandIsUnknown(): void
    {
        $task = new PHPStanTask();
        $task->setCommand('any unknown');

        $this->factory->createBuilder($task);
    }
}
