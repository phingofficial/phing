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

include_once 'phing/types/selectors/BaseExtendSelector.php';

/**
 * Selector that filters files based on the filename.
 *
 * @author Hans Lellelid, hans@xmpl.org (Phing)
 * @author Bruce Atherton, bruce@callenish.com (Ant)
 *
 * @package phing.types.selectors
 */
class FilenameSelector extends BaseExtendSelector
{
    private $pattern = null;
    private $regex = null;
    private $casesensitive = true;
    private $negated = false;
    const NAME_KEY = "name";
    const CASE_KEY = "casesensitive";
    const NEGATE_KEY = "negate";
    const REGEX_KEY = "regex";

    private $reg;
    private $expression;

    /**
     * @return string
     */
    public function __toString()
    {
        $buf = "{filenameselector name: ";
        if ($this->pattern !== null) {
            $buf .= $this->pattern;
        }
        if ($this->regex != null) {
            $buf .= $this->regex . " [as regular expression]";
        }
        $buf .= " negate: ";
        if ($this->negated) {
            $buf .= "true";
        } else {
            $buf .= "false";
        }
        $buf .= " casesensitive: ";
        if ($this->casesensitive) {
            $buf .= "true";
        } else {
            $buf .= "false";
        }
        $buf .= "}";

        return $buf;
    }

    /**
     * The name of the file, or the pattern for the name, that
     * should be used for selection.
     *
     * @param string $pattern the file pattern that any filename must match
     *                        against in order to be selected.
     *
     * @return void
     */
    public function setName($pattern)
    {
        $pattern = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $pattern);

        if (StringHelper::endsWith(DIRECTORY_SEPARATOR, $pattern)) {
            $pattern .= "**";
        }
        $this->pattern = $pattern;
    }

    /**
     * The regular expression the file name will be matched against.
     *
     * @param string $pattern the regular expression that any filename must match
     *                against in order to be selected.
     */
    public function setRegex($pattern)
    {
        $this->regex = $pattern;
        $this->reg = null;
    }

    /**
     * Whether to ignore case when checking filenames.
     *
     * @param bool $casesensitive whether to pay attention to case sensitivity
     *
     * @return void
     */
    public function setCasesensitive($casesensitive)
    {
        $this->casesensitive = $casesensitive;
    }

    /**
     * You can optionally reverse the selection of this selector,
     * thereby emulating an &lt;exclude&gt; tag, by setting the attribute
     * negate to true. This is identical to surrounding the selector
     * with &lt;not&gt;&lt;/not&gt;.
     *
     * @param bool $negated whether to negate this selection
     *
     * @return void
     */
    public function setNegate($negated)
    {
        $this->negated = $negated;
    }

    /**
     * When using this as a custom selector, this method will be called.
     * It translates each parameter into the appropriate setXXX() call.
     *
     * @param array $parameters the complete set of parameters for this selector
     *
     * @return void
     */
    public function setParameters($parameters)
    {
        parent::setParameters($parameters);
        if ($parameters !== null) {
            for ($i = 0, $len = count($parameters); $i < $len; $i++) {
                $paramname = $parameters[$i]->getName();
                switch (strtolower($paramname)) {
                    case self::NAME_KEY:
                        $this->setName($parameters[$i]->getValue());
                        break;
                    case self::CASE_KEY:
                        $this->setCasesensitive(Project::toBoolean($parameters[$i]->getValue()));
                        break;
                    case self::NEGATE_KEY:
                        $this->setNegate(Project::toBoolean($parameters[$i]->getValue()));
                        break;
                    case self::REGEX_KEY:
                        $this->setRegex($parameters[$i]->getValue());
                        break;
                    default:
                        $this->setError("Invalid parameter " . $paramname);
                }
            } // for each param
        } // if params
    }

    /**
     * Checks to make sure all settings are kosher. In this case, it
     * means that the name attribute has been set.
     *
     * {@inheritdoc}
     *
     * @return void
     */
    public function verifySettings()
    {
        if ($this->pattern === null && $this->regex === null) {
            $this->setError("The name or regex attribute is required");
        } elseif ($this->pattern !== null && $this->regex !== null) {
            $this->setError("Only one of name and regex attribute is allowed");
        }
    }

    /**
     * The heart of the matter. This is where the selector gets to decide
     * on the inclusion of a file in a particular fileset. Most of the work
     * for this selector is offloaded into SelectorUtils, a static class
     * that provides the same services for both FilenameSelector and
     * DirectoryScanner.
     *
     * {@inheritdoc}
     *
     * @param PhingFile $basedir the base directory the scan is being done from
     * @param string $filename is the name of the file to check
     * @param PhingFile $file is a PhingFile object the selector can use
     *
     * @return bool whether the file should be selected or not
     */
    public function isSelected(PhingFile $basedir, $filename, PhingFile $file)
    {
        $this->validate();

        if ($this->pattern !== null) {
            return (SelectorUtils::matchPath($this->pattern, $filename, $this->casesensitive)
                === !($this->negated));
        }
        if ($this->reg === null) {
            $this->reg = new RegularExpression();
            $this->reg->setPattern($this->regex);
            $this->expression = $this->reg->getRegexp($this->getProject());
        }
        $this->reg->setIgnoreCase(!$this->casesensitive);
        return $this->expression->matches($filename) === !$this->negated;
    }
}
