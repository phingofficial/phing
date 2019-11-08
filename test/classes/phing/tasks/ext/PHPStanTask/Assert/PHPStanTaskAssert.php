<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

class PHPStanTaskAssert extends Assert
{

    public function assertDefaults(PHPStanTask $task): void
    {
        self::assertEquals('phpstan', $task->getExecutable());
        self::assertEquals('analyse', $task->getCommand());

        $this->assertCommonDefaults($task);
        $this->assertAnalyseDefaults($task);
        $this->assertHelpDefaults($task);
        $this->assertListDefaults($task);
    }

    private function assertCommonDefaults(PHPStanTask $task): void
    {
        self::assertNull($task->isHelp());
        self::assertNull($task->isQuiet());
        self::assertNull($task->isVersion());
        self::assertNull($task->isANSI());
        self::assertNull($task->isNoANSI());
        self::assertNull($task->isNoInteraction());
        self::assertNull($task->isVerbose());
        self::assertNull($task->isCheckreturn());
    }

    private function assertAnalyseDefaults(PHPStanTask $task): void
    {
        self::assertNull($task->getConfiguration());
        self::assertNull($task->getLevel());
        self::assertNull($task->isNoProgress());
        self::assertNull($task->isDebug());
        self::assertNull($task->getAutoloadFile());
        self::assertNull($task->getErrorFormat());
        self::assertNull($task->getMemoryLimit());
        self::assertNull($task->getPaths());
    }

    private function assertHelpDefaults(PHPStanTask $task): void
    {
        self::assertNull($task->getFormat());
        self::assertNull($task->isRaw());
        self::assertNull($task->getCommandName());
    }

    private function assertListDefaults(PHPStanTask $task): void
    {
        self::assertNull($task->getFormat());
        self::assertNull($task->isRaw());
        self::assertNull($task->getNamespace());
    }
}
