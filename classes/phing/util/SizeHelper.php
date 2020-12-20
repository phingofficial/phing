<?php declare(strict_types=1);

/**
 * SizeHelper class
 *
 * @author Jawira Portugal <dev@tugal.be>
 */
class SizeHelper
{
    const UNITS = ['B', 'K', 'M', 'G', 'T', 'P'];
    const MXXIV = 1024;

    /**
     * Converts strings like '512K', '0.5G', '50M' to bytes.
     *
     * Bytes are always returned as an integer.
     * Upper case and lower case units are supported.
     *
     * @param string $human
     *
     * @return int
     */
    public static function fromHumanToBytes(string $human): int
    {
        list($size, $unit) = self::parseHuman($human);
        $bytes = $size * pow(self::MXXIV, array_search($unit, self::UNITS, true));

        return intval($bytes);
    }

    /**
     * Convert from bytes to any other valid unit.
     *
     * Upper case and lower case units are supported.
     *
     * @param int    $bytes
     * @param string $unit Unit is case sensitive.
     *
     * @return float
     */
    public static function fromBytesTo(int $bytes, string $unit): float
    {
        if (!self::isValidUnit($unit)) {
            throw new BuildException("Invalid size unit '$unit'");
        }
        $converted = $bytes / pow(self::MXXIV, array_search(strtoupper($unit), self::UNITS, true));

        return floatval($converted);
    }

    /**
     * Parses size and unit from strings like '1m', '50M', '100.55K', '2048'.
     *
     * Parsing is not locale aware.
     * Parsing is case insensitive.
     * Only first letter is parsed, therefore "100Bobo" will be valid for Bytes.
     *
     * @param string $human
     *
     * @return array{0: float, 1: string} First element is size, and second is the unit.
     */
    public static function parseHuman(string $human): array
    {
        // no units, so we assume bytes
        if (is_numeric($human)) {
            return [floatval($human), self::UNITS[0]];
        }
        $parsed = sscanf(strtoupper($human), '%f%1s');
        if (empty($parsed[0])) {
            throw new BuildException("Invalid size string '$human'");
        }
        if (!self::isValidUnit($parsed[1])) {
            throw new BuildException("Invalid size unit '${parsed[1]}'");
        }

        return $parsed;
    }

    /**
     * Tells you if a unit is supported by SizeHelper.
     *
     * Unit is case insensitive.
     *
     * @param string $unit One character unit.
     *
     * @return bool
     */
    public static function isValidUnit(string $unit): bool
    {
        return in_array(strtoupper($unit), self::UNITS, true);
    }
}
