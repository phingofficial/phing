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
 * Applies a native php function to the original input.
 *
 * Example:
 * <pre><phparraymaplines function="strtoupper"/></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.PhpArrayMapLines">
 *  <param name="function" value="strtoupper"/>
 * </filterreader></pre>
 *
 * @author  Siad Ardroumli <siad.ardroumli@gmail.com>
 * @package phing.filters
 */
class PhpArrayMapLines extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Parameter name for the function.
     */
    public const FUNCTION_KEY = 'function';

    /**
     * The function to be used.
     *
     * @var string
     */
    private $function = null;

    /**
     * Applies a native php function to the original input and returns resulting stream.
     *
     * @param int|null $len
     *
     * @return mixed buffer, -1 on EOF
     *
     * @throws IOException
     */
    public function read(?int $len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->checkAttributes();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);

        if ($buffer === -1 || !function_exists($this->function)) {
            return -1;
        }

        $lines = explode("\n", $buffer);

        $filtered = array_map($this->function, $lines);

        return implode("\n", $filtered);
    }

    /**
     * Sets the function used by array_map.
     *
     * @param string $function The function used by array_map.
     *
     * @return void
     */
    public function setFunction(string $function): void
    {
        $this->function = (string) $function;
    }

    /**
     * Returns the prefix which will be added at the start of each input line.
     *
     * @return string The prefix which will be added at the start of each input line
     */
    public function getFunction(): string
    {
        return $this->function;
    }

    /**
     * Make sure that required attributes are set.
     *
     * @return void
     *
     * @throws BuildException - if any required attribs aren't set.
     */
    protected function checkAttributes(): void
    {
        if (!$this->function) {
            throw new BuildException("You must specify a value for the 'function' attribute.");
        }
    }

    /**
     * Creates a new PhpArrayMapLines filter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return PhpArrayMapLines A new filter based on this configuration, but filtering
     *                          the specified reader
     */
    public function chain(Reader $reader): BaseFilterReader
    {
        $newFilter = new PhpArrayMapLines($reader);
        $newFilter->setFunction($this->getFunction());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Initializes the function if it is available from the parameters.
     *
     * @return void
     */
    private function initialize(): void
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $_i = count($params); $i < $_i; $i++) {
                if (self::FUNCTION_KEY == $params[$i]->getName()) {
                    $this->function = (string) $params[$i]->getValue();
                    break;
                }
            }
        }
    }
}
