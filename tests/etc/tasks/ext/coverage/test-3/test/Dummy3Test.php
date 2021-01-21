<?php

include __DIR__ . '/../src/Dummy3.php';

use PHPUnit\Framework\TestCase;

class Dummy3Test extends TestCase
{
    public function testDummy3()
    {
        $this->assertSame('foo', (new Dummy1('foo'))->result());
    }
}
