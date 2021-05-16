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

    /**
     * @dataProvider startsWithProvider
     * @covers       \Phing\Util\StringHelper::startsWith
     */
    public function testStartsWith($needle, $haystack, $expected)
    {
        $result = StringHelper::startsWith($needle, $haystack);
        $this->assertSame($expected, $result);
    }

    public function startsWithProvider()
    {
        return [
            // True
            ['F', 'FooBarBaz', true],
            ['Foo', 'FooBarBaz', true],
            ['FooBarBaz', 'FooBarBaz', true],
            ['', 'FooBarBaz', true],
            ['', "\x00", true],
            ["\x00", "\x00", true],
            ["\x00", "\x00a", true],
            ["a\x00b", "a\x00bc", true],
            // False
            ['Foo', 'BarBaz', false],
            ['foo', 'FooBarBaz', false],
            ['Foo', 'fooBarBaz', false],
            ['Foo', '', false],
        ];
    }

    /**
     * @dataProvider endsWithProvider
     * @covers       \Phing\Util\StringHelper::endsWith
     */
    public function testEndsWith($needle = 'o', $haystack = 'foo', $expected = true)
    {
        $result = StringHelper::endsWith($needle, $haystack);
        $this->assertSame($expected, $result);
    }


    public function endsWithProvider()
    {
        return [
            // True
            ['z', 'FooBarBaz', true],
            ['Baz', 'FooBarBaz', true],
            ['FooBarBaz', 'FooBarBaz', true],
            ['', 'FooBarBaz', true],
            ['', "\x00", true],
            ["\x00", "\x00", true],
            ["\x00", "a\x00", true],
            ["b\x00c", "ab\x00c", true],
            // False
            ['Baz', 'FooBar', false],
            ['baz', 'FooBarBaz', false],
            ['Baz', 'foobarbaz', false],
            ['Baz', '', false],
        ];
    }
}
