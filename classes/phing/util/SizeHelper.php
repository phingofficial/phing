<?php
declare(strict_types=1);
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

/**
 * SizeHelper class
 *
 * @author Jawira Portugal <dev@tugal.be>
 */
class SizeHelper
{
    const B    = 'B';
    const KILO = 1000;
    const KIBI = 1024;
    const SI   = [1 => ['kB', 'kilo', 'kilobyte',],
                  2 => ['MB', 'mega', 'megabyte',],
                  3 => ['GB', 'giga', 'gigabyte',],
                  4 => ['TB', 'tera', 'terabyte',],];
    const IEC  = [0 => [self::B,],
                  1 => ['k', 'Ki', 'KiB', 'kibi', 'kibibyte',],
                  2 => ['M', 'Mi', 'MiB', 'mebi', 'mebibyte',],
                  3 => ['G', 'Gi', 'GiB', 'gibi', 'gibibyte',],
                  4 => ['T', 'Ti', 'TiB', 'tebi', 'tebibyte',],];

    /**
     * Converts strings like '512K', '0.5G', '50M' to bytes.
     */
    public static function fromHumanToBytes(string $human): int
    {
        [$size, $unit] = self::parseHuman($human);
        $multiple = self::findUnitMultiple($unit);

        return intval($bytes);
    }

    /**
     * Convert from bytes to any other valid unit.
     */
    public static function fromBytesTo(int $bytes, string $unit): float
    {
        $multiple = self::findUnitMultiple($unit);

        return $bytes / $multiple;
    }

    /**
     * Convert from bytes to any other valid unit.
     */
    public static function fromBytesTo(int $bytes, string $unit): float
    {
        $unit      = self::normalizeUnit($unit);
        $converted = $bytes / pow(self::MXXIV, array_search($unit, self::UNITS, true));

        return floatval($converted);
    }

    /**
     * Finds the value in bytes of a single "unit".
     */
    protected static function findUnitMultiple(string $unit): int
    {
        foreach (self::IEC as $exponent => $choices) {
            if (in_array(strtolower($unit), array_map('strtolower', $choices))) {
                return pow(self::KIBI, $exponent);
            }
        }
        foreach (self::SI as $exponent => $choices) {
            if (in_array(strtolower($unit), array_map('strtolower', $choices))) {
                return pow(self::KILO, $exponent);
            }
        }
        throw new BuildException("Invalid unit '$unit'");
    }
}
