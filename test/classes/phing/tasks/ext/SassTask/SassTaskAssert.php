<?php

declare(strict_types=1);

use PHPUnit\Framework\Assert;

class SassTaskAssert extends Assert
{

    public function assertDefaults(SassTask $task): void
    {
        $this->assertEquals('', $task->getPath());
        $this->assertEquals('', $task->getOutputpath());
        $this->assertEquals('utf-8', $task->getEncoding());
        $this->assertEquals('nested', $task->getStyle());
        $this->assertEquals('css', $task->getNewext());
        $this->assertFalse($task->getTrace());
        $this->assertFalse($task->getCheck());
        $this->assertTrue($task->getUnixnewlines());
        $this->assertTrue($task->getKeepsubdirectories());
        $this->assertTrue($task->getRemoveoldext());
        $this->assertEquals('sass', $task->getExecutable(), "Executable is not 'sass'");
        $this->assertEquals('', $task->getExtfilter(), "Extfilter is not ''");
        $this->assertTrue($task->getRemoveoldext());
        $this->assertFalse($task->getCompressed());
        $this->assertFalse($task->getCompact());
        $this->assertFalse($task->getExpand());
        $this->assertFalse($task->getCrunched());
        $this->assertTrue($task->getNested());
    }
    
    public function assertCompactStyle(SassTask $task): void
    {
        $this->assertTrue($task->getCompact());
        $this->assertEquals('compact', $task->getStyle());
        $this->assertEquals('--style compact', $task->getFlags());
        $this->assertFalse($task->getCompressed());
        $this->assertFalse($task->getExpand());
        $this->assertFalse($task->getCrunched());
        $this->assertFalse($task->getNested());
    }

    public function assertCompressedStyle(SassTask $task): void
    {
        $this->assertTrue($task->getCompressed());
        $this->assertEquals('compressed', $task->getStyle());
        $this->assertEquals('--style compressed', $task->getFlags());
        $this->assertFalse($task->getCompact());
        $this->assertFalse($task->getExpand());
        $this->assertFalse($task->getCrunched());
        $this->assertFalse($task->getNested());
    }
}
