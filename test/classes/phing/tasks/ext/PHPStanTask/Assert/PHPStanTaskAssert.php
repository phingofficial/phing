<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

class PHPStanTaskAssert extends Assert
{

    public function assertDefaults(PHPStanTask $task): void
    {
        $this->assertEquals('phpstan', $task->getExecutable());
        $this->assertEquals('analyse', $task->getCommand());

        $this->assertCommonDefaults($task);
        $this->assertAnalyseDefaults($task);
        $this->assertHelpDefaults($task);
        $this->assertListDefaults($task);
    }

    private function assertCommonDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->isHelp());
        $this->assertNull($task->isQuiet());
        $this->assertNull($task->isVersion());
        $this->assertNull($task->isANSI());
        $this->assertNull($task->isNoANSI());
        $this->assertNull($task->isNoInteraction());
        $this->assertNull($task->isVerbose());
    }

    private function assertAnalyseDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->getConfiguration());
        $this->assertNull($task->getLevel());
        $this->assertNull($task->isNoProgress());
        $this->assertNull($task->isDebug());
        $this->assertNull($task->getAutoloadFile());
        $this->assertNull($task->getErrorFormat());
        $this->assertNull($task->getMemoryLimit());
        $this->assertNull($task->getPaths());
    }

    private function assertHelpDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->getFormat());
        $this->assertNull($task->isRaw());
        $this->assertNull($task->getCommandName());
    }

    private function assertListDefaults(PHPStanTask $task): void
    {
        $this->assertNull($task->getFormat());
        $this->assertNull($task->isRaw());
        $this->assertNull($task->getNamespace());
    }
}
