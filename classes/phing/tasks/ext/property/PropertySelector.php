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
 * PropertySelector Task
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.tasks.ext.property
 */
class PropertySelector extends AbstractPropertySetterTask
{
    /**
     * @var RegularExpression $match
     */
    private $match;

    /**
     * @var string
     */
    private $select = "\\0";

    /**
     * @var string
     */
    private $delim = ',';

    /**
     * @var bool
     */
    private $caseSensitive = true;

    /**
     * @var bool
     */
    private $distinct = false;

    /**
     * @param string $match
     *
     * @return void
     */
    public function setMatch(string $match): void
    {
        $this->match = new RegularExpression();
        $this->match->setPattern($match);
    }

    /**
     * @param string $select
     *
     * @return void
     */
    public function setSelect(string $select): void
    {
        $this->select = $select;
    }

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
     * @param string $delim
     *
     * @return void
     */
    public function setDelimiter(string $delim): void
    {
        $this->delim = $delim;
    }

    /**
     * @param bool $distinct
     *
     * @return void
     */
    public function setDistinct(bool $distinct): void
    {
        $this->distinct = $distinct;
    }

    /**
     * @return void
     */
    protected function validate(): void
    {
        parent::validate();
        if ($this->match == null) {
            throw new BuildException('No match expression specified.');
        }
    }

    /**
     * @return void
     *
     * @throws RegexpException
     */
    public function main(): void
    {
        $this->validate();

        $regex = $this->match->getRegexp($this->project);
        $regex->setIgnoreCase(!$this->caseSensitive);
        $props = $this->project->getProperties();
        $e     = array_keys($props);
        $buf   = '';
        $cnt   = 0;

        $used = [];

        foreach ($e as $key) {
            if ($regex->matches($key)) {
                $output = $this->select;
                $groups = $regex->getGroups();
                $sz     = count($groups);
                for ($i = 0; $i < $sz; $i++) {
                    $s = $groups[$i];

                    $result = new RegularExpression();
                    $result->setPattern('\\\\' . $i);
                    $sregex = $result->getRegexp($this->project);
                    $sregex->setReplace($output);
                    $output = $sregex->replace($s);
                }

                if (!($this->distinct && in_array($output, $used))) {
                    $used[] = $output;
                    if ($cnt !== 0) {
                        $buf .= $this->delim;
                    }
                    $buf .= $output;
                    $cnt++;
                }
            }
        }

        if ($buf !== '') {
            $this->setPropertyValue($buf);
        }
    }
}
