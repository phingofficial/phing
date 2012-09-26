<?php

namespace Foo;

class BarTest extends \PHPUnit_Framework_TestCase
{
    public function testGetString()
    {
        $bar = new Bar();
        
        $this->assertEquals('baz', $bar->getString());
    }
}