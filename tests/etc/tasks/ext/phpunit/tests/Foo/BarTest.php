<?php

/**
 * @internal
 * @coversNothing
 */
class Foo_BarTest extends \PHPUnit\Framework\TestCase
{
    public function testGetString()
    {
        $bar = new Foo_Bar();

        $this->assertEquals('baz', $bar->getString());
    }
}
