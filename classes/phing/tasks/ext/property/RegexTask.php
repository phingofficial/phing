<?php
/**
 * $Id$
 *
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

include_once 'phing/tasks/ext/property/AbstractPropertySetterTask.php';

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
 * @author    Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package   phing.tasks.regex
 */
class RegexTask extends AbstractPropertySetterTask
{
    /** @var string $subject */
    private $subject;

    /** @var string $pattern */
    private $pattern;

    /** @var string $match */
    private $match;

    /** @var string $replace */
    private $replace;

    /** @var string $delimiter */
    private $delimiter = '/';

    /** @var int $limit */
    private $limit = -1;

    /** @var string $defaultValue */
    private $defaultValue;

    /** @var bool $caseSensitive */
    private $caseSensitive = true;

    /** @var array $modifiers */
    private $modifiers = array(
        'PCRE_CASELESS'  => 'i'
    );

    /**
     * @param $subject
     */
    public function setSubject($subject)
    {
        $this->subject = preg_quote($subject, $this->delimiter);
    }

    /**
     * @param $limit
     */
    public function setLimit($limit)
    {
        $this->log('Set limit to ' . $limit, Project::MSG_DEBUG);

        $this->limit = $limit;
    }

    /**
     * @param $defaultValue
     */
    public function setDefaultValue($defaultValue)
    {
        $this->log('Set default value to ' . $defaultValue, Project::MSG_DEBUG);

        $this->defaultValue = $defaultValue;
    }

    /**
     * @param $pattern
     * @throws BuildException
     */
    public function setPattern($pattern)
    {
        if ($this->pattern !== null) {
            throw new BuildException("Cannot specify more than one regular expression");
        }

        $pattern = addslashes($pattern);
        $this->log('Set pattern to ' . $pattern, Project::MSG_DEBUG);

        $this->pattern = $pattern;
    }

    /**
     * @param $replace
     * @throws BuildException
     */
    public function setReplace($replace)
    {
        if ($this->replace !== null) {
            throw new BuildException("Cannot specify more than one replace expression");
        }
        if ($this->match !== null) {
            throw new BuildException("You cannot specify both a select and replace expression");
        }

        $this->log('Set replace to ' . $replace, Project::MSG_DEBUG);

        $this->replace = $replace;
    }

    /**
     * @param $match
     * @throws BuildException
     */
    public function setMatch($match)
    {
        if ($this->match !== null) {
            throw new BuildException("Cannot specify more than one match expression");
        }

        $this->log('Set match to ' . $match, Project::MSG_DEBUG);

        $this->match = $match;
    }

    /**
     * @param $caseSensitive
     */
    public function setCaseSensitive($caseSensitive)
    {

        $this->log('Set case-sensitive to ' . $caseSensitive, Project::MSG_DEBUG);

        $this->caseSensitive = $caseSensitive;
    }

    /**
     * @return mixed|string
     * @throws BuildException
     */
    protected function doReplace()
    {
        if ($this->replace === null) {
            throw new BuildException("No replace expression specified.");
        }
        $options = '';
        if (!$this->caseSensitive) {
            $options .= $this->modifiers['PCRE_CASELESS'];
        }

        $pattern = sprintf('%s%s%s%s', $this->delimiter, $this->pattern, $this->delimiter, $options);
        $output = preg_replace($pattern, $this->replace, $this->subject, $this->limit);

        if ($this->subject === $output || $output === null) {
            $output = $this->defaultValue;
        }

        return $output;
    }

    /**
     * @return string
     */
    protected function doSelect()
    {
        $options = '';
        if (!$this->caseSensitive) {
            $options .= $this->modifiers['PCRE_CASELESS'];
        }

        $pattern = sprintf('%s%s%s%s', $this->delimiter, $this->pattern, $this->delimiter, $options);
        $group = ltrim($this->match, '$');

        $output = $this->defaultValue;

        if (preg_match($pattern, $this->subject, $matches)) {
            $output = $matches[(int) $group];
        }

        return $output;
    }

    /**
     * @throws BuildException
     */
    protected function validate()
    {
        if ($this->pattern === null) {
            throw new BuildException("No match expression specified.");
        }
        if ($this->replace === null && $this->match === null) {
            throw new BuildException("You must specify either a preg_replace or preg_match pattern");
        }
    }

    /**
     * @throws BuildException
     */
    public function main()
    {
        $this->validate();

        $output = $this->match;

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
