<?php

include __DIR__ . '/../src/Dummy2.php';

use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
class Dummy2Test extends TestCase
{
    public function testDummy1()
    {
        $this->assertSame('foo', (new Dummy1('foo'))->result());
    }
}
