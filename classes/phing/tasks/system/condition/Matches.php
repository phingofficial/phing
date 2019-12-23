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
 * Simple regular expression condition.
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.system.condition
 */
class Matches extends ProjectComponent implements Condition
{
    /**
     * @var string $string
     */
    private $string;

    /**
     * @var RegularExpression $regularExpression
     */
    private $regularExpression;

    /**
     * @var bool $multiLine
     */
    private $multiLine = false;

    /**
     * @var bool $caseSensitive
     */
    private $caseSensitive = true;

    /**
     * @var string $modifiers
     */
    private $modifiers;

    /**
     * @param bool $caseSensitive
     *
     * @return void
     */
    public function setCaseSensitive(bool $caseSensitive): void
    {
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Whether to match should be multiline.
     *
     * @param bool $multiLine
     *
     * @return void
     */
    public function setMultiLine(bool $multiLine): void
    {
        $this->multiLine = $multiLine;
    }

    /**
     * @param string $pattern
     *
     * @return void
     *
     * @throws BuildException
     */
    public function setPattern(string $pattern): void
    {
        if ($this->regularExpression !== null) {
            throw new BuildException('Only one regular expression is allowed.');
        }
        $this->regularExpression = new RegularExpression();
        $this->regularExpression->setPattern($pattern);
    }

    /**
     * The string to match
     *
     * @param string $string
     *
     * @return void
     */
    public function setString(string $string): void
    {
        $this->string = $string;
    }

    /**
     * @param string $modifiers
     *
     * @return void
     */
    public function setModifiers(string $modifiers): void
    {
        $this->modifiers = $modifiers;
    }

    /**
     * @return bool
     *
     * @throws RegexpException
     */
    public function evaluate(): bool
    {
        if ($this->string === null) {
            throw new BuildException('Parameter string is required in matches.');
        }
        if ($this->regularExpression === null) {
            throw new BuildException('Missing pattern in matches.');
        }
        $this->regularExpression->setMultiline($this->multiLine);
        $this->regularExpression->setIgnoreCase(!$this->caseSensitive);
        $this->regularExpression->setModifiers($this->modifiers);
        $regexp = $this->regularExpression->getRegexp($this->getProject());

        return $regexp->matches($this->string);
    }
}
