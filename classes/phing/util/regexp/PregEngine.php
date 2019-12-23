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

declare(strict_types=1);

/**
 * PREG Regexp Engine.
 * Implements a regexp engine using PHP's preg_match(), preg_match_all(), and preg_replace() functions.
 *
 * @author  hans lellelid, hans@velum.net
 * @package phing.util.regexp
 */
class PregEngine implements RegexpEngine
{
    /**
     * Set to null by default to distinguish between false and not set
     *
     * @var bool
     */
    private $ignoreCase = null;

    /**
     * Set to null by default to distinguish between false and not set
     *
     * @var bool
     */
    private $multiline = null;

    /**
     * Pattern modifiers
     *
     * @link http://php.net/manual/en/reference.pcre.pattern.modifiers.php
     *
     * @var string|null
     */
    private $modifiers = null;

    /**
     * Set the limit.
     *
     * @var int
     */
    private $limit = -1;

    /**
     * Pattern delimiter.
     */
    public const DELIMITER = '`';

    /**
     * Sets pattern modifiers for regex engine
     *
     * @param string|null $mods Modifiers to be applied to a given regex
     *
     * @return void
     */
    public function setModifiers(?string $mods): void
    {
        $this->modifiers = (string) $mods;
    }

    /**
     * Gets pattern modifiers.
     *
     * @return string|null
     */
    public function getModifiers(): ?string
    {
        $mods = (string) $this->modifiers;
        if ($this->getIgnoreCase()) {
            $mods .= 'i';
        } elseif ($this->getIgnoreCase() === false) {
            $mods = str_replace('i', '', $mods);
        }
        if ($this->getMultiline()) {
            $mods .= 's';
        } elseif ($this->getMultiline() === false) {
            $mods = str_replace('s', '', $mods);
        }
        // filter out duplicates
        $mods = preg_split('//', (string) $mods, -1, PREG_SPLIT_NO_EMPTY);
        $mods = implode('', array_unique($mods));

        return $mods;
    }

    /**
     * Sets whether or not regex operation is case sensitive.
     *
     * @param bool $bit
     *
     * @return void
     */
    public function setIgnoreCase(bool $bit): void
    {
        $this->ignoreCase = (bool) $bit;
    }

    /**
     * Gets whether or not regex operation is case sensitive.
     *
     * @return bool|null
     */
    public function getIgnoreCase(): ?bool
    {
        return $this->ignoreCase;
    }

    /**
     * Sets whether regexp should be applied in multiline mode.
     *
     * @param bool $bit
     *
     * @return void
     */
    public function setMultiline(bool $bit): void
    {
        $this->multiline = $bit;
    }

    /**
     * Gets whether regexp is to be applied in multiline mode.
     *
     * @return bool|null
     */
    public function getMultiline(): ?bool
    {
        return $this->multiline;
    }

    /**
     * Sets the maximum possible replacements for each pattern.
     *
     * @param int $limit
     *
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Returns the maximum possible replacements for each pattern.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * The pattern needs to be converted into PREG style -- which includes adding expression delims & any flags, etc.
     *
     * @param string $pattern
     *
     * @return string prepared pattern.
     */
    private function preparePattern(string $pattern): string
    {
        $delimiterPattern = '/\\\\*' . self::DELIMITER . '/';

        // The following block escapes usages of the delimiter in the pattern if it's not already escaped.
        if (preg_match_all($delimiterPattern, $pattern, $matches, PREG_OFFSET_CAPTURE)) {
            $diffOffset = 0;

            foreach ($matches[0] as $match) {
                $str    = $match[0];
                $offset = $match[1] + $diffOffset;

                $escStr = strlen($str) % 2 ? '\\' . $str : $str; // This will increase an even number of backslashes, before a forward slash, to an odd number.  I.e. '\\/' becomes '\\\/'.

                $diffOffset += strlen($escStr) - strlen($str);

                $pattern = substr_replace($pattern, $escStr, $offset, strlen($str));
            }
        }

        return self::DELIMITER . $pattern . self::DELIMITER . $this->getModifiers();
    }

    /**
     * Matches pattern against source string and sets the matches array.
     *
     * @param string     $pattern The regex pattern to match.
     * @param string     $source  The source string.
     * @param array|null $matches The array in which to store matches.
     *
     * @return bool Success of matching operation.
     */
    public function match(string $pattern, string $source, ?array &$matches): bool
    {
        return preg_match($this->preparePattern($pattern), $source, $matches) > 0;
    }

    /**
     * Matches all patterns in source string and sets the matches array.
     *
     * @param string     $pattern The regex pattern to match.
     * @param string     $source  The source string.
     * @param array|null $matches The array in which to store matches.
     *
     * @return bool Success of matching operation.
     */
    public function matchAll(string $pattern, string $source, ?array &$matches): bool
    {
        return preg_match_all($this->preparePattern($pattern), $source, $matches) > 0;
    }

    /**
     * Replaces $pattern with $replace in $source string.
     * References to \1 group matches will be replaced with more preg-friendly
     * $1.
     *
     * @param string $pattern The regex pattern to match.
     * @param string $replace The string with which to replace matches.
     * @param string $source  The source string.
     *
     * @return string The replaced source string.
     */
    public function replace(string $pattern, string $replace, string $source): string
    {
        // convert \1 -> $1, because we want to use the more generic \1 in the XML
        // but PREG prefers $1 syntax.
        $replace = preg_replace('/\\\(\d+)/', '\$$1', $replace);

        return preg_replace($this->preparePattern($pattern), $replace, $source, $this->limit);
    }
}
