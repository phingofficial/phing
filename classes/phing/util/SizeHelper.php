<?php
declare(strict_types=1);

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
     */
    public static function fromHumanToBytes(string $human): int
    {
        list($size, $unit) = self::parseHuman($human);
        $bytes = $size * pow(self::MXXIV, array_search($unit, self::UNITS, true));

        return intval($bytes);
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
     * Extracts size and unit from strings like '1m', '50M', '100.55K', '2048'.
     *
     * This function can also handle scientific notation, e.g. '18e10k'.
     * For sake of completeness it can also handle negative values '-1M'.
     * Parsing is not locale aware, meaning that '.' (dot) is always used as decimal separator.
     * Parsing is not case-sensitive, '10m' and '10M' are equivalent.
     * Only first letter is parsed, therefore "100Bob" will be parsed as '100B' (100 bytes).
     *
     * @param string $human
     *
     * @return array{0: float, 1: string} First element is size, and second is the unit.
     */
    public static function parseHuman(string $human): array
    {
        // no unit, so we assume bytes
        if (is_numeric($human)) {
            return [floatval($human), self::UNITS[0]];
        }
        $parsed = sscanf(strtoupper($human), '%f%s');
        if (empty($parsed[0])) {
            throw new BuildException("Invalid size '$human'");
        }
        $parsed[1] = self::normalizeUnit($parsed[1]);

        return $parsed;
    }

    /**
     * Normalizes unit to a valid unit if possible.
     *
     * Any string can be passed, but only first character will be used.
     *
     * @param string $unit
     *
     * @return string One character valid unit.
     */
    protected static function normalizeUnit(string $unit): string
    {
        if ($unit === '') {
            throw new BuildException('Unit string is empty');
        }
        $normalized = strtoupper($unit[0]);
        if (!in_array($normalized, self::UNITS, true)) {
            throw new BuildException("Invalid unit '$unit'");
        }

        return $normalized;
    }
}
