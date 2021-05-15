<?php

namespace Phing\Test\Util;

use Phing\Util\StringHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 */
class StringHelperTest extends TestCase
{
    /**
     * @dataProvider booleanValueProvider
     * @covers       \Phing\Util\StringHelper::booleanValue
     *
     * @param mixed $candidate
     */
    public function testBooleanValue($candidate, $expected)
    {
        $result = StringHelper::booleanValue($candidate);
        $this->assertIsBool($result);
        $this->assertSame($expected, $result);
    }

    public function booleanValueProvider(): array
    {
        return [
            // True values
            ['on', true],
            ['ON', true],
            ['On', true],
            ['  on  ', true],
            ['true', true],
            ['True', true],
            ['TrUe', true],
            ['TRUE', true],
            ['    true', true],
            ['yes', true],
            ['Yes', true],
            ['YeS', true],
            ['YES', true],
            [' YES    ', true],
            ['1', true],
            [' 1   ', true],
            [1, true],
            [1.0, true],
            [true, true],
            // False values
            ['Off', false],
            ['   Off ', false],
            ['false', false],
            ['False', false],
            [' False ', false],
            ['no', false],
            ['NO', false],
            ['  NO   ', false],
            ['foo', false],
            ['', false],
            ['t', false],
            ['f', false],
            ['    ', false],
            [[], false],
            [['foo', 'bar'], false],
            [false, false],
            ['0', false],
            [0, false],
            [1.1, false],
            [123, false],
            [new stdClass(), false],
            [null, false],
        ];
    }

    /**
     * @dataProvider isBooleanProvider
     * @covers       \Phing\Util\StringHelper::isBoolean
     * @param string $candidate
     * @param bool   $expected
     */
    public function testIsBoolean($candidate, $expected)
    {
        $result = StringHelper::isBoolean($candidate);
        $this->assertIsBool($result);
        $this->assertSame($expected, $result);
    }

    public function isBooleanProvider()
    {
        return [
            // Boolean values
            ['on', true],
            ['ON', true],
            ['On', true],
            ['  on  ', true],
            ['true', true],
            ['True', true],
            ['TrUe', true],
            ['TRUE', true],
            ['    true', true],
            ['yes', true],
            ['Yes', true],
            ['YeS', true],
            ['YES', true],
            [' YES    ', true],
            ['1', true],
            [' 1   ', true],
            ['Off', true],
            ['   Off ', true],
            ['false', true],
            ['False', true],
            [' False ', true],
            ['no', true],
            ['NO', true],
            ['  NO   ', true],
            ['0', true],
            [' 0 ', true],
            [true, true],
            [false, true],
            // Not boolean values
            ['    ', false],
            [1.0, false],
            [0, false],
            [1, false],
            ['foo', false],
            ['', false],
            ['t', false],
            ['f', false],
            [[], false],
            [['foo', 'bar'], false],
            [1.1, false],
            [123, false],
            [new stdClass(), false],
            [null, false],
        ];
    }
}
