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
 * Abstract class for writing character streams.
 *
 * @package phing.system.io
 */
abstract class Writer
{
    /**
     * Writes data to output stream.
     *
     * @param string   $buf
     * @param int|null $off
     * @param int|null $len
     *
     * @return void
     */
    abstract public function write(string $buf, ?int $off = null, ?int $len = null): void;

    /**
     * Close the stream.
     *
     * @return void
     *
     * @throws IOException - if there is an error closing stream.
     */
    abstract public function close(): void;

    /**
     * Flush the stream, if supported by the stream.
     *
     * @return void
     */
    public function flush(): void
    {
    }

    /**
     * Returns a string representation of resource filename, url, etc. that is being written to.
     *
     * @return mixed
     */
    abstract public function getResource();
}
