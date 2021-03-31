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

/**
 * String helper utility class.
 *
 * This class includes some Java-like functions for parsing strings,
 * as well as some functions for getting qualifiers / unqualifying phing-style
 * classpaths.  (e.g. "phing.util.StringHelper").
 *
 * @author Hans Lellelid <hans@xmpl.org>
 *
 */
class StringHelper
{
    /**
     * @var array
     */
    private static $TRUE_VALUES = ["on", "true", "t", "yes", "1"];

    /**
     * @var array
     */
    private static $FALSE_VALUES = ["off", "false", "f", "no", "0"];

    /**
     * @param bool|string $s
     *
     * @return bool
     */
    public static function booleanValue($s)
    {
        if (is_bool($s)) {
            return $s; // it's already bool (not a string)
        }
        // otherwise assume it's something like "true" or "t"
        $trimmed = strtolower(trim($s));

        return (bool) in_array($trimmed, self::$TRUE_VALUES);
    }

    /**
     * tests if a string is a representative of a boolean
     *
     * @param bool|string $s
     *
     * @return bool
     */
    public static function isBoolean($s)
    {
        if (is_bool($s)) {
            return true; // it already is boolean
        }

        if ($s === "" || $s === null || !is_string($s)) {
            return false; // not a valid string for testing
        }

        $test = strtolower(trim($s));

        return in_array($test, array_merge(self::$FALSE_VALUES, self::$TRUE_VALUES), true);
    }

    /**
     * tests if a string starts with a given string
     *
     * @param string $check
     * @param string $string
     *
     * @return bool
     */
    public static function startsWith($check, $string)
    {
        if ($check === "" || $check === $string) {
            return true;
        }

        return strpos((string) $string, $check) === 0;
    }

    /**
     * tests if a string ends with a given string
     *
     * @param string $check
     * @param string $string
     *
     * @return bool
     */
    public static function endsWith($check, $string)
    {
        if ($check === "" || $check === $string) {
            return true;
        }

        return strpos(strrev($string), strrev($check)) === 0;
    }

    /**
     * a natural way of getting a subtring, php's circular string buffer and strange
     * return values suck if you want to program strict as of C or friends
     *
     * @param string $string
     * @param int    $startpos
     * @param int    $endpos
     *
     * @return string
     */
    public static function substring($string, $startpos, $endpos = -1)
    {
        $len = strlen($string);
        $endpos = (int) (($endpos === -1) ? $len - 1 : $endpos);
        if ($startpos > $len - 1 || $startpos < 0) {
            trigger_error("substring(), Startindex out of bounds must be 0<n<$len", E_USER_ERROR);
        }
        if ($endpos > $len - 1 || $endpos < $startpos) {
            trigger_error("substring(), Endindex out of bounds must be $startpos<n<" . ($len - 1), E_USER_ERROR);
        }
        if ($startpos === $endpos) {
            return (string) $string[$startpos];
        }

        $len = $endpos - $startpos;

        return substr((string) $string, $startpos, $len + 1);
    }

    /**
     * Does the value correspond to a slot variable?
     *
     * @param string $value
     *
     * @return bool|int
     */
    public static function isSlotVar($value)
    {
        $value = trim($value);
        if ($value === "") {
            return false;
        }

        return preg_match('/^%\{([\w\.\-]+)\}$/', $value);
    }

    /**
     * Extracts the variable name for a slot var in the format %{task.current_file}
     *
     * @param string $var The var from build file.
     *
     * @return string Extracted name part.
     */
    public static function slotVar($var)
    {
        return trim($var, '%{} ');
    }
}
