<?php

include __DIR__ . '/../src/Dummy1.php';

use PHPUnit\Framework\TestCase;

class Dummy1Test extends TestCase
{
    public function testDummy1()
    {
        $this->assertSame('foo', (new Dummy1('foo'))->result());
    }
}
