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
 * Wrapper class for readers, which can be used to apply filters.
 *
 * @package phing.system.io
 */
class FilterReader extends Reader
{
    /**
     * @var Reader|null
     */
    protected $in;

    /**
     * @param Reader|null $in
     */
    public function __construct(?Reader $in = null)
    {
        $this->in = $in;
    }

    /**
     * @param Reader $in
     *
     * @return void
     */
    public function setReader(Reader $in): void
    {
        $this->in = $in;
    }

    /**
     * @param int $n
     *
     * @return int
     */
    public function skip(int $n): int
    {
        return $this->in->skip($n);
    }

    /**
     * Read data from source.
     * FIXME: Clean up this function signature, as it a) params aren't being used
     * and b) it doesn't make much sense.
     *
     * @param int|null $len
     *
     * @return mixed
     *
     * @throws IOException
     */
    public function read(?int $len = null)
    {
        return $this->in->read($len);
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->in->reset();
    }

    /**
     * @return void
     *
     * @throws IOException
     */
    public function close(): void
    {
        $this->in->close();
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->in->getResource();
    }
}
