<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class PHPStanTaskUnitTest extends TestCase
{

    public function testItHasValidDefaults(): void
    {
        $task = new PHPStanTask();
        $assert = new PHPStanTaskAssert();

        $assert->assertDefaults($task);
    }
}