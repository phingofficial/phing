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
 * Regular Expression Task for properties.
 *
 * <pre>
 *   <propertyregex property="pack.name"
 *                  subject="package.ABC.name"
 *                  pattern="package\.([^.]*)\.name"
 *                  match="$1"
 *                  casesensitive="false"
 *                  defaultvalue="test1"/>
 *
 *   <echo message="${pack.name}"/>
 *
 *   <propertyregex property="pack.name"
 *                  override="true"
 *                  subject="package.ABC.name"
 *                  pattern="(package)\.[^.]*\.(name)"
 *                  replace="$1.DEF.$2"
 *                  casesensitive="false"
 *                  defaultvalue="test2"/>
 *
 *   <echo message="${pack.name}"/>
 *
 * </pre>
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.regex
 */
class RegexTask extends AbstractPropertySetterTask
{
    /**
     * @var string $subject
     */
    private $subject;

    /**
     * @var string $pattern
     */
    private $pattern;

    /**
     * @var string $match
     */
    private $match;

    /**
     * @var string $replace
     */
    private $replace;

    /**
     * @var string $defaultValue
     */
    private $defaultValue;

    /**
     * @var bool $caseSensitive
     */
    private $caseSensitive = true;

    /**
     * @var string $modifiers
     */
    private $modifiers = '';

    /**
     * @var Regexp $reg
     */
    private $reg;

    /**
     * @var int $limit
     */
    private $limit = -1;

    /**
     * @return void
     */
    public function init(): void
    {
        $this->reg = new Regexp();
    }

    /**
     * @param int $limit
     *
     * @return void
     */
    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * @param string $subject
     *
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    /**
     * @param string $defaultValue
     *
     * @return void
     *
     * @throws Exception
     */
    public function setDefaultValue(string $defaultValue): void
    {
        $this->log('Set default value to ' . $defaultValue, Project::MSG_DEBUG);

        $this->defaultValue = $defaultValue;
    }

    /**
     * @param string $pattern
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function setPattern(string $pattern): void
    {
        if ($this->pattern !== null) {
            throw new BuildException(
                'Cannot specify more than one regular expression'
            );
        }

        $this->log('Set pattern to ' . $pattern, Project::MSG_DEBUG);

        $this->pattern = $pattern;
    }

    /**
     * @param string $replace
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function setReplace(string $replace): void
    {
        if ($this->replace !== null) {
            throw new BuildException(
                'Cannot specify more than one replace expression'
            );
        }
        if ($this->match !== null) {
            throw new BuildException(
                'You cannot specify both a select and replace expression'
            );
        }

        $this->log('Set replace to ' . $replace, Project::MSG_DEBUG);

        $this->replace = $replace;
    }

    /**
     * @param string $match
     *
     * @return void
     *
     * @throws Exception
     * @throws BuildException
     */
    public function setMatch(string $match): void
    {
        if ($this->match !== null) {
            throw new BuildException(
                'Cannot specify more than one match expression'
            );
        }

        $this->log('Set match to ' . $match, Project::MSG_DEBUG);

        $this->match = $match;
    }

    /**
     * @param bool $caseSensitive
     *
     * @return void
     *
     * @throws Exception
     */
    public function setCaseSensitive(bool $caseSensitive): void
    {
        $this->log('Set case-sensitive to ' . $caseSensitive, Project::MSG_DEBUG);

        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @return string
     *
     * @throws BuildException
     */
    protected function doReplace(): string
    {
        if ($this->replace === null) {
            throw new BuildException('No replace expression specified.');
        }
        $this->reg->setPattern($this->pattern);
        $this->reg->setReplace($this->replace);
        $this->reg->setModifiers($this->modifiers);
        $this->reg->setIgnoreCase(!$this->caseSensitive);
        $this->reg->setLimit($this->limit);

        try {
            $output = $this->reg->replace($this->subject);
        } catch (Throwable $e) {
            $output = $this->defaultValue;
        }

        return $output;
    }

    /**
     * @return string
     *
     * @throws BuildException
     */
    protected function doSelect(): string
    {
        $this->reg->setPattern($this->pattern);
        $this->reg->setModifiers($this->modifiers);
        $this->reg->setIgnoreCase(!$this->caseSensitive);

        $output = $this->defaultValue;

        try {
            if ($this->reg->matches($this->subject)) {
                $output = $this->reg->getGroup((int) ltrim($this->match, '$'));
            }
        } catch (Throwable $e) {
            throw new BuildException($e);
        }

        return $output;
    }

    /**
     * @return void
     *
     * @throws BuildException
     */
    protected function validate(): void
    {
        if ($this->pattern === null) {
            throw new BuildException('No match expression specified.');
        }
        if ($this->replace === null && $this->match === null) {
            throw new BuildException(
                'You must specify either a preg_replace or preg_match pattern'
            );
        }
    }

    /**
     * @return void
     *
     * @throws BuildException
     */
    public function main(): void
    {
        $this->validate();

        if ($this->replace !== null) {
            $output = $this->doReplace();
        } else {
            $output = $this->doSelect();
        }

        if ($output !== null) {
            $this->setPropertyValue($output);
        }
    }
}
