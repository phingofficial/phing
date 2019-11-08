<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

class SassTaskAssert extends Assert
{

    public function assertDefaults(SassTask $task): void
    {
        self::assertEquals('', $task->getPath());
        self::assertEquals('', $task->getOutputpath());
        self::assertEquals('utf-8', $task->getEncoding());
        self::assertEquals('nested', $task->getStyle());
        self::assertEquals('css', $task->getNewext());
        self::assertFalse($task->getTrace());
        self::assertFalse($task->getCheck());
        self::assertTrue($task->getUnixnewlines());
        self::assertTrue($task->getKeepsubdirectories());
        self::assertTrue($task->getRemoveoldext());
        self::assertEquals('sass', $task->getExecutable(), "Executable is not 'sass'");
        self::assertEquals('', $task->getExtfilter(), "Extfilter is not ''");
        self::assertTrue($task->getRemoveoldext());
        self::assertFalse($task->getCompressed());
        self::assertFalse($task->getCompact());
        self::assertFalse($task->getExpand());
        self::assertFalse($task->getCrunched());
        self::assertTrue($task->getNested());
    }

    public function assertCompactStyle(SassTask $task): void
    {
        self::assertTrue($task->getCompact());
        self::assertEquals('compact', $task->getStyle());
        self::assertEquals('--style compact', $task->getFlags());
        self::assertFalse($task->getCompressed());
        self::assertFalse($task->getExpand());
        self::assertFalse($task->getCrunched());
        self::assertFalse($task->getNested());
    }

    public function assertCompressedStyle(SassTask $task): void
    {
        self::assertTrue($task->getCompressed());
        self::assertEquals('compressed', $task->getStyle());
        self::assertEquals('--style compressed', $task->getFlags());
        self::assertFalse($task->getCompact());
        self::assertFalse($task->getExpand());
        self::assertFalse($task->getCrunched());
        self::assertFalse($task->getNested());
    }
}
