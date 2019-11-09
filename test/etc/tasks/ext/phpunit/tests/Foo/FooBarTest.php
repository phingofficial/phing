<?php

class FooBarTest extends \PHPUnit\Framework\TestCase
{
    public function testGetString()
    {
        $bar = new FooBar();

        $this->assertEquals('baz', $bar->getString());
    }
}
