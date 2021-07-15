<?php

/**
 * @internal
 * @coversNothing
 */
class provider_failTest extends \PHPUnit\Framework\TestCase
{
    public function testSimplefail()
    {
        $this->assertFalse(true);
    }

    /**
     * @dataProvider provider
     *
     * @param mixed $v1
     * @param mixed $v2
     */
    public function testProvider($v1, $v2)
    {
        $this->assertEquals($v1, $v2);
    }

    public function provider()
    {
        return [
            [true, true],
        ];
    }
}
