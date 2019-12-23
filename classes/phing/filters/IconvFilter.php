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
 * Encode data from <code>in</code> encoding to <code>out</code> encoding.
 *
 * Example:
 * <pre>
 * <iconvfilter inputencoding="UTF-8" outputencoding="CP1251" />
 * </pre>
 * Or:
 * <pre>
 * <filterreader classname="phing.filters.IconvFilter">
 *    <param name="inputencoding" value="UTF-8" />
 *    <param name="outputencoding" value="CP1251" />
 * </filterreader>
 * </pre>
 *
 * @author  Alexey Shockov, <alexey@shockov.com>
 * @package phing.filters
 */
class IconvFilter extends BaseParamFilterReader implements ChainableReader
{
    /**
     * @var string
     */
    private $inputEncoding;

    /**
     * @var string
     */
    private $outputEncoding;

    /**
     * Returns first n lines of stream.
     *
     * @param int|null $len
     *
     * @return string|int Characters read, or -1 if the end of the stream has been reached
     *
     * @throws IOException if the underlying stream throws an IOException during reading
     */
    public function read(?int $len = null)
    {
        $this->initialize();

        // Process whole text at once.
        $text = null;
        while (($data = $this->in->read($len)) !== -1) {
            $text .= $data;
        }

        // At the end.
        if (null === $text) {
            return -1;
        }

        $this->log(
            'Encoding ' . $this->in->getResource() . ' from ' . $this->getInputEncoding() . ' to ' . $this->getOutputEncoding(),
            Project::MSG_VERBOSE
        );

        return iconv($this->inputEncoding, $this->outputEncoding, $text);
    }

    /**
     * @param string $encoding Input encoding.
     *
     * @return void
     */
    public function setInputEncoding(string $encoding): void
    {
        $this->inputEncoding = $encoding;
    }

    /**
     * @return string
     */
    public function getInputEncoding(): string
    {
        return $this->inputEncoding;
    }

    /**
     * @param string $encoding Output encoding.
     *
     * @return void
     */
    public function setOutputEncoding(string $encoding): void
    {
        $this->outputEncoding = $encoding;
    }

    /**
     * @return string
     */
    public function getOutputEncoding(): string
    {
        return $this->outputEncoding;
    }

    /**
     * Creates a new IconvFilter using the passed in Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream. Must not be <code>null</code>.
     *
     * @return self A new filter based on this configuration, but filtering the specified reader.
     */
    public function chain(Reader $reader): BaseFilterReader
    {
        $filter = new self($reader);

        $filter->setInputEncoding($this->getInputEncoding());
        $filter->setOutputEncoding($this->getOutputEncoding());

        $filter->setInitialized(true);
        $filter->setProject($this->getProject());

        return $filter;
    }

    /**
     * Configuring object from the parameters list.
     *
     * @return void
     */
    private function initialize(): void
    {
        if ($this->getInitialized()) {
            return;
        }

        $params = $this->getParameters();
        if ($params !== null) {
            foreach ($params as $param) {
                if ('in' === $param->getName()) {
                    $this->setInputEncoding($param->getValue());
                } else {
                    if ('out' === $param->getName()) {
                        $this->setOutputEncoding($param->getValue());
                    }
                }
            }
        }

        $this->setInitialized(true);
    }
}
