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
    private $select        = "\\0";
    private $delim         = ',';
    private $caseSensitive = true;
    private $distinct      = false;

    public function setMatch($match)
    {
        $this->match = new RegularExpression();
        $this->match->setPattern($match);
    }

    public function setSelect($select)
    {
        $this->select = $select;
    }

    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = $caseSensitive;
    }

    public function setDelimiter($delim)
    {
        $this->delim = $delim;
    }

    public function setDistinct($distinct)
    {
        $this->distinct = $distinct;
    }

    protected function validate()
    {
        parent::validate();
        if ($this->match == null) {
            throw new BuildException('No match expression specified.');
        }
    }

    public function main()
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
