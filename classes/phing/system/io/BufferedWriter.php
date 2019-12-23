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
 * Convenience class for writing files.
 *
 * @author  Hans Lellelid <hans@xmpl.org>
 * @package phing.system.io
 */
class BufferedWriter extends Writer
{
    /**
     * @var int The size of the buffer in kb.
     */
    private $bufferSize = 0;

    /**
     * @var Writer The Writer we are buffering output to.
     */
    private $out;

    /**
     * @param Writer $writer
     * @param int    $buffsize
     */
    public function __construct(Writer $writer, int $buffsize = 8192)
    {
        $this->out        = $writer;
        $this->bufferSize = $buffsize;
    }

    /**
     * @param string   $buf
     * @param int|null $off
     * @param int|null $len
     *
     * @return void
     */
    public function write(string $buf, ?int $off = null, ?int $len = null): void
    {
        $this->out->write($buf, $off, $len);
    }

    /**
     * @return void
     */
    public function newLine(): void
    {
        $this->write(PHP_EOL);
    }

    /**
     * @return mixed
     */
    public function getResource()
    {
        return $this->out->getResource();
    }

    /**
     * @return void
     */
    public function flush(): void
    {
        $this->out->flush();
    }

    /**
     * Close attached stream.
     *
     * @return void
     *
     * @throws IOException
     */
    public function close(): void
    {
        $this->out->close();
    }
}
