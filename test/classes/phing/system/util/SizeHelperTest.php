<?php
declare(strict_types=1);


use PHPUnit\Framework\TestCase;

class SizeHelperTest extends TestCase
{
    /**
     * @dataProvider fromHumanToBytesProvider
     */
    public function testFromHumanToBytes(string $humanSize, $expectedBytes)
    {
        $bytes = SizeHelper::fromHumanToBytes($humanSize);
        $this->assertIsInt($bytes);
        $this->assertSame($expectedBytes, $bytes);
    }

    public function fromHumanToBytesProvider()
    {
        return [
            ['1024', 1024],
            ['0', 0],
            ['13.20B', 13],
            ['13.20b', 13],
            ['10K', 10240],
            ['10k', 10240],
            ['153.12M', 160557957],
            ['153.12m', 160557957],
            ['5G', 5368709120],
            ['5g', 5368709120],
            ['00.1T', 109951162777],
            ['00.1t', 109951162777],
            ['1P', 1125899906842624],
            ['1p', 1125899906842624],
        ];
    }

    /**
     * @dataProvider fromBytesToProvider
     */
    public function testFromBytesTo(int $bytes, string $unit, float $expected)
    {
        $converted = SizeHelper::fromBytesTo($bytes, $unit);
        $this->assertSame($expected, $converted);
    }

    public function fromBytesToProvider()
    {
        return [
            [1024, 'B', 1024],
            [1024, 'b', 1024],
            [1024, 'K', 1],
            [1024, 'k', 1],
        ];
    }

    /**
     * @dataProvider invalidFromBytesToProvider
     */
    public function testInvalidFromBytesTo(string $unit, string $message)
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage($message);
        SizeHelper::fromBytesTo(1024, $unit);
    }


    public function invalidFromBytesToProvider()
    {
        return [
            ['', "Unit string is empty"],
            ["\t", "Invalid unit '\t'"],
            ['-', "Invalid unit '-'"],
            ['x', "Invalid unit 'x'"],
            ['E', "Invalid unit 'E'"],
            ['Z', "Invalid unit 'Z'"],
            ['Y', "Invalid unit 'Y'"],
            ['hello', "Invalid unit 'hello'"],
        ];
    }

    /**
     * @dataProvider validParseHumanProvider
     */
    public function testValidParseHuman(string $humanSize, float $expectedSize, string $expectedUnit)
    {
        $parsed = SizeHelper::parseHuman($humanSize);
        list($size, $unit) = $parsed;
        $this->assertIsArray($parsed);
        $this->assertIsFloat($size);
        $this->assertIsString($unit);
        $this->assertSame($expectedSize, $size);
        $this->assertSame($expectedUnit, $unit);
    }

    public function validParseHumanProvider()
    {
        return [
            ['0', 0, 'B'],
            ['18e10', 180000000000.0, 'B'],
            ['-10', -10, 'B'],
            ['1024', 1024, 'B'],
            ['1b', 1, 'B'],
            ['30.50B', 30.5, 'B'],
            ['94.5008Bobo', 94.5008, 'B'],
            ['18e10k', 180000000000.0, 'K'],
            ['17k', 17, 'K'],
            ['700.0005K', 700.0005, 'K'],
            ['15Kilo', 15.0000, 'K'],
            ['100.0005Ken', 100.0005, 'K'],
            ['13.01m', 13.01, 'M'],
            ['1.1234M', 1.1234, 'M'],
            ['57Megas', 57.0, 'M'],
            ['63mama', 63.0, 'M'],
            ['77.03g', 77.03, 'G'],
            ['16.81G', 16.81, 'G'],
            ['56.23Giga', 56.23, 'G'],
            ['16.37go', 16.37, 'G'],
            ['9t', 9.0, 'T'],
            ['96T', 96.0, 'T'],
            ['24.50000Tera', 24.5, 'T'],
            ['49p', 49.0, 'P'],
            ['3P', 3.0, 'P'],
            ['72peta', 72.0, 'P'],
            ['44pie', 44.0, 'P'],
        ];
    }

    /**
     * @dataProvider invalidParseHumanProvider
     */
    public function testInvalidParseHuman(string $humanSize, string $message)
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage($message);
        SizeHelper::parseHuman($humanSize);
    }

    public function invalidParseHumanProvider()
    {
        return [
            ['', "Invalid size ''"],
            ['+', "Invalid size '+'"],
            ['--', "Invalid size '--'"],
            ['M', "Invalid size 'M'"],
            ['M50', "Invalid size 'M50'"],
            ['90x', "Invalid unit 'X'"],
            ['10E', "Invalid unit 'E'"],
            ['10Z', "Invalid unit 'Z'"],
            ['10Y', "Invalid unit 'Y'"],
        ];
    }
}
