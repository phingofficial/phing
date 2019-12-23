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
 * Filter to flatten the stream to a single line.
 *
 * Example:
 *
 * <pre><striplinebreaks/></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.StripLineBreaks"/></pre>
 *
 * @see     BaseParamFilterReader
 *
 * @author  <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author  hans lellelid, hans@velum.net
 * @package phing.filters
 */
class StripLineBreaks extends BaseParamFilterReader implements ChainableReader
{
    /**
     * Default line-breaking characters.
     */
    public const DEFAULT_LINE_BREAKS = "\r\n";

    /**
     * Parameter name for the line-breaking characters parameter.
     */
    public const LINES_BREAKS_KEY = 'linebreaks';

    /**
     * The characters that are recognized as line breaks.
     *
     * @var string
     */
    private $lineBreaks = "\r\n"; // self::DEFAULT_LINE_BREAKS;

    /**
     * Returns the filtered stream, only including
     * characters not in the set of line-breaking characters.
     *
     * @param int|null $len
     *
     * @return mixed The resulting stream, or -1
     *               if the end of the resulting stream has been reached.
     *
     * @throws IOException If the underlying stream throws an IOException
     *                     during reading
     */
    public function read(?int $len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $buffer = $this->in->read($len);
        if ($buffer === -1) {
            return -1;
        }

        $buffer = preg_replace('/[' . $this->lineBreaks . ']/', '', $buffer);

        return $buffer;
    }

    /**
     * Sets the line-breaking characters.
     *
     * @param string $lineBreaks A String containing all the characters to be
     *                           considered as line-breaking.
     *
     * @return void
     */
    public function setLineBreaks(string $lineBreaks): void
    {
        $this->lineBreaks = (string) $lineBreaks;
    }

    /**
     * Gets the line-breaking characters.
     *
     * @return string A String containing all the characters that are considered as line-breaking.
     */
    public function getLineBreaks(): string
    {
        return $this->lineBreaks;
    }

    /**
     * Creates a new StripLineBreaks using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return StripLineBreaks A new filter based on this configuration, but filtering
     *                         the specified reader
     */
    public function chain(Reader $reader): BaseFilterReader
    {
        $newFilter = new StripLineBreaks($reader);
        $newFilter->setLineBreaks($this->getLineBreaks());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Parses the parameters to set the line-breaking characters.
     *
     * @return void
     */
    private function initialize(): void
    {
        $userDefinedLineBreaks = null;
        $params                = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $paramsCount = count($params); $i < $paramsCount; $i++) {
                if (self::LINES_BREAKS_KEY === $params[$i]->getName()) {
                    $userDefinedLineBreaks = $params[$i]->getValue();
                    break;
                }
            }
        }

        if ($userDefinedLineBreaks !== null) {
            $this->lineBreaks = $userDefinedLineBreaks;
        }
    }
}
