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
 * Is one string part of another string?
 *
 * @author  Hans Lellelid <hans@xmpl.org> (Phing)
 * @author  Stefan Bodewig <stefan.bodewig@epost.de> (Ant)
 * @package phing.tasks.system.condition
 */
class ContainsCondition implements Condition
{
    /**
     * @var string
     */
    private $string;

    /**
     * @var string
     */
    private $subString;

    /**
     * @var bool
     */
    private $caseSensitive = true;

    /**
     * The string to search in.
     *
     * @param string $a1
     *
     * @return void
     */
    public function setString(string $a1): void
    {
        $this->string = $a1;
    }

    /**
     * The string to search for.
     *
     * @param string $a2
     *
     * @return void
     */
    public function setSubstring(string $a2): void
    {
        $this->subString = $a2;
    }

    /**
     * Whether to search ignoring case or not.
     *
     * @param bool $b
     *
     * @return void
     */
    public function setCaseSensitive(bool $b): void
    {
        $this->caseSensitive = (bool) $b;
    }

    /**
     * Check whether string contains substring.
     *
     * @return bool
     *
     * @throws BuildException
     */
    public function evaluate(): bool
    {
        if ($this->string === null || $this->subString === null) {
            throw new BuildException(
                'both string and substring are required '
                . 'in contains'
            );
        }

        return $this->caseSensitive
            ? strpos($this->string, $this->subString) !== false
            : stripos($this->string, $this->subString) !== false;
    }
}
