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
 * Filter which includes only those lines that contain all the user-specified
 * strings.
 *
 * Example:
 *
 * <pre><linecontains>
 *   <contains value="foo">
 *   <contains value="bar">
 * </linecontains></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.LineContains">
 *    <param type="contains" value="foo"/>
 *    <param type="contains" value="bar"/>
 * </filterreader></pre>
 *
 * This will include only those lines that contain <code>foo</code> and
 * <code>bar</code>.
 *
 * @author  Yannick Lecaillez <yl@seasonfive.com>
 * @author  Hans Lellelid <hans@velum.net>
 * @see     PhingFilterReader
 * @package phing.filters
 */
class LineContains extends BaseParamFilterReader implements ChainableReader
{

    /**
     * The parameter name for the string to match on.
     *
     * @var string
     */
    const CONTAINS_KEY = "contains";
    const NEGATE_KEY = 'negate';

    /**
     * Array of Contains objects.
     *
     * @var array
     */
    private $_contains = [];

    /**
     * @var bool $negate
     */
    private $negate = false;

    /**
     * Returns all lines in a buffer that contain specified strings.
     *
     * @param  null $len
     * @return mixed buffer, -1 on EOF
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->_initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);

        if ($buffer === -1) {
            return -1;
        }

        $lines = explode("\n", $buffer);
        $matched = [];
        $containsSize = count($this->_contains);

        foreach ($lines as $line) {
            for ($i = 0; $i < $containsSize; $i++) {
                $containsStr = $this->_contains[$i]->getValue();
                if (false === strpos($line, $containsStr)) {
                    $line = null;
                    break;
                }
            }
            if ($line !== null) {
                $matched[] = $line;
            }
        }
        $filtered_buffer = implode("\n", $matched);

        if ($this->isNegated()) {
            $filtered_buffer = implode("\n", array_diff($lines, $matched));
        }

        return $filtered_buffer;
    }

    /**
     * Set the negation mode.  Default false (no negation).
     *
     * @param boolean $b the boolean negation mode to set.
     */
    public function setNegate($b)
    {
        $this->negate = (bool) $b;
    }

    /**
     * Find out whether we have been negated.
     *
     * @return boolean negation flag.
     */
    public function isNegated()
    {
        return $this->negate;
    }

    /**
     * Adds a <code>contains</code> nested element.
     *
     * @return Contains The <code>contains</code> element added.
     *                  Must not be <code>null</code>.
     */
    public function createContains()
    {
        $num = array_push($this->_contains, new Contains());

        return $this->_contains[$num - 1];
    }

    /**
     * Sets the array of words which must be contained within a line read
     * from the original stream in order for it to match this filter.
     *
     * @param  array $contains An array of words which must be contained
     *                        within a line in order for it to match in this filter.
     *                        Must not be <code>null</code>.
     * @throws Exception
     */
    public function setContains($contains)
    {
        // type check, error must never occur, bad code of it does
        if (!is_array($contains)) {
            throw new Exception("Expected array got something else");
        }

        $this->_contains = $contains;
    }

    /**
     * Returns the vector of words which must be contained within a line read
     * from the original stream in order for it to match this filter.
     *
     * @return array The array of words which must be contained within a line read
     *               from the original stream in order for it to match this filter. The
     *               returned object is "live" - in other words, changes made to the
     *               returned object are mirrored in the filter.
     */
    public function getContains()
    {
        return $this->_contains;
    }

    /**
     * Creates a new LineContains using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return LineContains A new filter based on this configuration, but filtering
     *                      the specified reader
     */
    public function chain(Reader $reader)
    {
        $newFilter = new LineContains($reader);
        $newFilter->setContains($this->getContains());
        $newFilter->setNegate($this->isNegated());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Parses the parameters to add user-defined contains strings.
     */
    private function _initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            foreach ($params as $param) {
                if (self::CONTAINS_KEY == $param->getType()) {
                    $cont = new Contains();
                    $cont->setValue($param->getValue());
                    $this->_contains[] = $cont;
                } elseif (self::NEGATE_KEY === $param->getType()) {
                    $this->setNegate(Project::toBoolean($param->getValue()));
                }
            }
        }
    }
}
