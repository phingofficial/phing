<?php

namespace Phing\Test\Util;

use Phing\Util\StringHelper;
use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @internal
 * @coversNothing
 */
class StringHelperTest extends TestCase
{
    /**
     * @dataProvider booleanValueProvider
     * @covers       \Phing\Util\StringHelper::booleanValue
     *
     * @param mixed $candidate
     * @param mixed $expected
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
     *
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
     *
     * @param mixed $needle
     * @param mixed $haystack
     * @param mixed $expected
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
     *
     * @param mixed $needle
     * @param mixed $haystack
     * @param mixed $expected
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

    /**
     * @covers \Phing\Util\StringHelper::substring
     */
    public function testSubstringSimple()
    {
        $result = StringHelper::substring('FooBarBaz', 3);
        $this->assertSame('BarBaz', $result);
    }

    /**
     * @covers       \Phing\Util\StringHelper::substring
     * @dataProvider substringProvider
     *
     * @param mixed $string
     * @param mixed $start
     * @param mixed $end
     * @param mixed $expected
     */
    public function testSubstring($string, $start, $end, $expected)
    {
        $result = StringHelper::substring($string, $start, $end);
        $this->assertSame($expected, $result);
    }

    public function substringProvider()
    {
        return [
            ['FooBarBaz', 0, 0, 'F'],
            ['FooBarBaz', 0, 1, 'Fo'],
            ['FooBarBaz', 2, 4, 'oBa'],
            ['FooBarBaz', 0, 0, 'F'],
            ['FooBarBaz', 3, 3, 'B'],
            ['FooBarBaz', 0, 8, 'FooBarBaz'],
            ['FooBarBaz', 0, -1, 'FooBarBaz'],
            ['FooBarBaz', 5, 8, 'rBaz'],
            ['FooBarBaz', 5, -1, 'rBaz'],
            ['FooBarBaz', 8, 8, 'z'],
        ];
    }

    /**
     * @covers       \Phing\Util\StringHelper::substring
     * @dataProvider substringErrorProvider
     *
     * @param mixed $string
     * @param mixed $start
     * @param mixed $end
     * @param mixed $message
     */
    public function testSubstringError($string, $start, $end, $message)
    {
        $this->expectError();
        $this->expectErrorMessage($message);
        StringHelper::substring($string, $start, $end);
    }

    public function substringErrorProvider()
    {
        return [
            ['FooBarBaz', -1, 1, 'substring(), Startindex out of bounds must be 0<n<9'],
            ['FooBarBaz', -10, 100, 'substring(), Startindex out of bounds must be 0<n<9'],
            ['FooBarBaz', 100, 1, 'substring(), Startindex out of bounds must be 0<n<9'],
            ['FooBarBaz', 0, 100, 'substring(), Endindex out of bounds must be 0<n<8'],
            ['FooBarBaz', 3, 1, 'substring(), Endindex out of bounds must be 3<n<8'],
        ];
    }

    /**
     * @covers       \Phing\Util\StringHelper::isSlotVar
     * @dataProvider isSlotVarProvider
     *
     * @param mixed $value
     * @param mixed $expected
     */
    public function testIsSlotVar($value, $expected)
    {
        $result = StringHelper::isSlotVar($value);
        $this->assertSame($expected, $result);
    }

    public function isSlotVarProvider()
    {
        return [
            // 1
            ['%{x}', 1],
            ['%{dummy}', 1],
            ['%{my.var}', 1],
            ['%{Foo.Bar.Baz}', 1],
            ['%{user.first-name}', 1],
            ['%{user.first_name}', 1],
            ['    %{slot.var}   ', 1],
            // 0
            ['slot.var', 0],
            ['%{&é@}', 0],
            ['%{slot§var}', 0],
            ['%{}', 0],
            ['%{slotèvar}', 0],
            ['%{slot%var}', 0],
            ['%{    slot.var       }', 0],
            ['}%slot.var{', 0],
        ];
    }

    /**
     * @covers \Phing\Util\StringHelper::slotVar
     * @dataProvider slotVarProvider
     *
     * @param mixed $var
     * @param mixed $expected
     */
    public function testSlotVar($var, $expected)
    {
        $result = StringHelper::slotVar($var);
        $this->assertSame($expected, $result);
    }

    public function slotVarProvider()
    {
        return [
            ['%{slot.var}', 'slot.var'],
            ['%{&é@}', '&é@'],
            ['', ''],
            ['%{}', ''],
            ['%{    }', ''],
            ['  %{  slot.var  }  ', 'slot.var'],
            ['FooBarBaz', 'FooBarBaz'],
        ];
    }
}
