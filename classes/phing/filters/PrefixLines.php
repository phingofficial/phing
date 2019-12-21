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
 * Attaches a prefix to every line.
 *
 * Example:
 * <pre><prefixlines prefix="Foo"/></pre>
 *
 * Or:
 *
 * <pre><filterreader classname="phing.filters.PrefixLines">
 *  <param name="prefix" value="Foo"/>
 * </filterreader></pre>
 *
 * @see     FilterReader
 *
 * @author  <a href="mailto:yl@seasonfive.com">Yannick Lecaillez</a>
 * @author  hans lellelid, hans@velum.net
 * @package phing.filters
 */
class PrefixLines extends BaseParamFilterReader implements ChainableReader
{

    /**
     * Parameter name for the prefix.
     */
    public const PREFIX_KEY = "lines";

    /**
     * The prefix to be used.
     *
     * @var string
     */
    private $prefix;

    /** @var string|null $queuedData */
    private $queuedData;

    /**
     * Adds a prefix to each line of input stream and returns resulting stream.
     *
     * @param int $len
     *
     * @return mixed buffer, -1 on EOF
     *
     * @throws IOException
     */
    public function read($len = null)
    {
        if (!$this->getInitialized()) {
            $this->initialize();
            $this->setInitialized(true);
        }

        $ch = -1;

        if ($this->queuedData !== null && $this->queuedData === '') {
            $this->queuedData = null;
        }

        if ($this->queuedData !== null) {
            $ch               = $this->queuedData[0];
            $this->queuedData = (string) substr($this->queuedData, 1);
            if ($this->queuedData === '') {
                $this->queuedData = null;
            }
        } else {
            $this->queuedData = $this->readLine();
            if ($this->queuedData === null) {
                $ch = -1;
            } else {
                if ($this->prefix !== null) {
                    $this->queuedData = $this->prefix . $this->queuedData;
                }
                return $this->read();
            }
        }

        return $ch;
    }

    /**
     * Sets the prefix to add at the start of each input line.
     *
     * @param string $prefix The prefix to add at the start of each input line.
     *                       May be <code>null</code>, in which case no prefix
     *                       is added.
     */
    public function setPrefix($prefix)
    {
        $this->prefix = (string) $prefix;
    }

    /**
     * Returns the prefix which will be added at the start of each input line.
     *
     * @return string The prefix which will be added at the start of each input line
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Creates a new PrefixLines filter using the passed in
     * Reader for instantiation.
     *
     * @param Reader $reader A Reader object providing the underlying stream.
     *                       Must not be <code>null</code>.
     *
     * @return PrefixLines A new filter based on this configuration, but filtering
     *                     the specified reader
     */
    public function chain(Reader $reader): Reader
    {
        $newFilter = new PrefixLines($reader);
        $newFilter->setPrefix($this->getPrefix());
        $newFilter->setInitialized(true);
        $newFilter->setProject($this->getProject());

        return $newFilter;
    }

    /**
     * Initializes the prefix if it is available from the parameters.
     */
    private function initialize()
    {
        $params = $this->getParameters();
        if ($params !== null) {
            for ($i = 0, $_i = count($params); $i < $_i; $i++) {
                if (self::PREFIX_KEY == $params[$i]->getName()) {
                    $this->prefix = (string) $params[$i]->getValue();
                    break;
                }
            }
        }
    }
}
