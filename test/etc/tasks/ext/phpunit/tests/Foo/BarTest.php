<?php

class Foo_BarTest extends \PHPUnit\Framework\TestCase
{
    public function testGetString()
    {
        $bar = new Foo_Bar();

        self::assertEquals('baz', $bar->getString());
    }
}
