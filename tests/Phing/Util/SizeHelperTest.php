<?php
/**
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */

namespace Phing\Util;

use Phing\Exception\BuildException;
use Phing\Util\SizeHelper;
use PHPUnit\Framework\TestCase;

class SizeHelperTest extends TestCase
{
    /**
     * @dataProvider fromHumanToBytesProvider
     */
    public function testFromHumanToBytes(string $humanSize, $expectedBytes)
    {
        $bytes = SizeHelper::fromHumanToBytes($humanSize);
        $this->assertSame($expectedBytes, $bytes);
    }

    public function fromHumanToBytesProvider(): array
    {
        return [
            ['1024', 1024.0],
            ['0', 0.0],
            ['-10', -10.0],
            ['13.20 B', 13.2],
            ['13.20b', 13.2],
            ['0.3 K', 307.2],
            ['0.3 ki', 307.2],
            ['0.3 K', 307.2],
            ['0.3 ki', 307.2],
            ['0.3 Kibi', 307.2],
            ['0.3 Kibibyte', 307.2],
            ['5.5e2kb', 550000.0],
            ['10kb', 10000.0],
            ['10kilo', 10000.0],
            ['10kilobyte', 10000.0],
            ['-10ki', -10240.0],
            [' 10kibi ', 10240.0],
            ["\t\n10ki\t\n", 10240.0],
            ['    10        kilo     ', 10000.0],
            ['153.12M', 160557957.12],
            ['153.12Mi', 160557957.12],
            ['153.12MiB', 160557957.12],
            ['153.12mebi', 160557957.12],
            ['153.12MebiByte', 160557957.12],
            ['5G', 5368709120.0],
            ['5gi', 5368709120.0],
            ['5gib', 5368709120.0],
            ['5gibi', 5368709120.0],
            ['5gibibyte', 5368709120.0],
            ['5gb', 5000000000.0],
            ['5giga', 5000000000.0],
            ['5gigabyte', 5000000000.0],
            ['00.1T', 109951162777.6],
            ['00.1ti', 109951162777.6],
            ['00.1tib', 109951162777.6],
            ['00.1tebi', 109951162777.6],
            ['00.1tebibyte', 109951162777.6],
            ['00.1tb', 100000000000.0],
            ['00.1tera', 100000000000.0],
            ['00.1terabyte', 100000000000.0],
        ];
    }

    /**
     * @dataProvider invalidFromHumanToBytesProvider
     */
    public function testInvalidFromHumanToBytes(string $human, string $message)
    {
        $this->expectException(BuildException::class);
        $this->expectExceptionMessage($message);
        SizeHelper::fromHumanToBytes($human);
    }

    public function invalidFromHumanToBytesProvider(): array
    {
        return [
            ['', "Invalid size ''"],
            ['+', "Invalid size '+'"],
            ['--', "Invalid size '--'"],
            ['m', "Invalid size 'm'"],
            ['M50', "Invalid size 'M50'"],
            ['90x', "Invalid unit 'x'"],
            ['10E', "Invalid unit 'E'"],
            ['10Z', "Invalid unit 'Z'"],
            ['10Y', "Invalid unit 'Y'"],
            ['3Hello', "Invalid unit 'Hello'"],
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

    public function fromBytesToProvider(): array
    {
        return [
            [1024, 'B', 1024],
            [1024, 'b', 1024],
            [2048, 'k', 2],
            [2048, 'ki', 2],
            [2048, 'kib', 2],
            [2048, 'kibi', 2],
            [2048, 'kibibyte', 2],
            [2500, 'Kb', 2.5],
            [2500, 'kilo', 2.5],
            [2500, 'kilobyte', 2.5],
            [3145728, 'M', 3],
            [3145728, 'MI', 3],
            [3145728, 'MIB', 3],
            [3145728, 'MEBI', 3],
            [3145728, 'MEBIBYTE', 3],
            [3500000, 'MB', 3.5],
            [3500000, 'MEGA', 3.5],
            [3500000, 'MEGABYTE', 3.5],
            [4294967296, 'g', 4],
            [4294967296, 'gi', 4],
            [4294967296, 'gib', 4],
            [4294967296, 'gibi', 4],
            [4294967296, 'gibibyte', 4],
            [4500000000, 'gb', 4.5],
            [4500000000, 'giga', 4.5],
            [4500000000, 'gigabyte', 4.5],
            [5497558138880, 'T', 5],
            [5497558138880, 'Ti', 5],
            [5497558138880, 'Tib', 5],
            [5497558138880, 'Tebi', 5],
            [5497558138880, 'Tebibyte', 5],
            [5500000000000, 'Tb', 5.5],
            [5500000000000, 'Tera', 5.5],
            [5500000000000, 'Terabyte', 5.5],
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

    public function invalidFromBytesToProvider(): array
    {
        return [
            ['', "Invalid unit ''"],
            ["\t", "Invalid unit '\t'"],
            ['-', "Invalid unit '-'"],
            ['  B  ', "Invalid unit '  B  '"],
            ['x', "Invalid unit 'x'"],
            ['E', "Invalid unit 'E'"],
            ['Z', "Invalid unit 'Z'"],
            ['Y', "Invalid unit 'Y'"],
            ['Hello', "Invalid unit 'Hello'"],
        ];
    }
}
