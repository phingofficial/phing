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
 * Contains some shared attributes and methods -- and some abstract methods with
 * engine-specific implementations that sub-classes must override.
 *
 * @author  Hans Lellelid <hans@velum.net>
 * @package phing.util.regexp
 */
interface RegexpEngine
{
    /**
     * Sets whether or not regex operation should ingore case.
     *
     * @param bool $bit
     *
     * @return void
     */
    public function setIgnoreCase(bool $bit): void;

    /**
     * Returns status of ignore case flag.
     *
     * @return bool|null
     */
    public function getIgnoreCase(): ?bool;

    /**
     * Sets whether regexp should be applied in multiline mode.
     *
     * @param bool $bit
     *
     * @return void
     */
    public function setMultiline(bool $bit): void;

    /**
     * Gets whether regexp is to be applied in multiline mode.
     *
     * @return bool|null
     */
    public function getMultiline(): ?bool;

    /**
     * Sets the maximum possible replacements for each pattern.
     *
     * @param int $limit
     *
     * @return void
     */
    public function setLimit(int $limit): void;

    /**
     * Returns the maximum possible replacements for each pattern.
     *
     * @return int
     */
    public function getLimit(): int;

    /**
     * Matches pattern against source string and sets the matches array.
     *
     * @param string $pattern The regex pattern to match.
     * @param string $source  The source string.
     * @param array  $matches The array in which to store matches.
     *
     * @return bool Success of matching operation.
     */
    public function match(string $pattern, string $source, array &$matches): bool;

    /**
     * Matches all patterns in source string and sets the matches array.
     *
     * @param string $pattern The regex pattern to match.
     * @param string $source  The source string.
     * @param array  $matches The array in which to store matches.
     *
     * @return bool Success of matching operation.
     */
    public function matchAll(string $pattern, string $source, array &$matches): bool;

    /**
     * Replaces $pattern with $replace in $source string.
     *
     * @param string $pattern The regex pattern to match.
     * @param string $replace The string with which to replace matches.
     * @param string $source  The source string.
     *
     * @return string The replaced source string.
     */
    public function replace(string $pattern, string $replace, string $source): string;
}
