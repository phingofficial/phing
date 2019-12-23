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
 * Extended file stream wrapper class which auto-creates directories
 *
 * @author  Michiel Rook <mrook@php.net>
 * @package phing.util
 */
class ExtendedFileStream
{
    private $fp = null;

    /**
     * @return void
     */
    public static function registerStream(): void
    {
        if (!in_array('efile', stream_get_wrappers())) {
            stream_wrapper_register('efile', 'ExtendedFileStream');
        }
    }

    /**
     * @return void
     */
    public static function unregisterStream(): void
    {
        stream_wrapper_unregister('efile');
    }

    /**
     * @param PhingFile|string $path
     *
     * @return void
     *
     * @throws IOException
     * @throws NullPointerException
     */
    private function createDirectories($path): void
    {
        $f = new PhingFile($path);
        if (!$f->exists()) {
            $f->mkdirs();
        }
    }

    // @codingStandardsIgnoreStart PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    // phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
    /**
     * @param string $path
     * @param int    $mode
     * @param mixed  $options
     * @param mixed  $opened_path
     *
     * @return bool
     *
     * @throws IOException
     * @throws \NullPointerException
     */
    public function stream_open(string $path, int $mode, $options, &$opened_path): bool
    {
        // if we're on Windows, urldecode() the path again
        if (FileSystem::getFileSystem()->getSeparator() == '\\') {
            $path = urldecode($path);
        }

        $filepath = substr($path, 8);

        $this->createDirectories(dirname($filepath));

        $this->fp = fopen($filepath, $mode);

        if (!$this->fp) {
            throw new BuildException("Unable to open stream for path {$path}");
        }

        return true;
    }

    /**
     * @return void
     */
    public function stream_close(): void
    {
        fclose($this->fp);
        $this->fp = null;
    }

    /**
     * @param int $count
     *
     * @return string
     */
    public function stream_read(int $count): string
    {
        return fread($this->fp, $count);
    }

    /**
     * @param string $data
     * @return int|false
     */
    public function stream_write(string $data)
    {
        return fwrite($this->fp, $data);
    }

    /**
     * @return bool
     */
    public function stream_eof(): bool
    {
        return feof($this->fp);
    }

    /**
     * @return false|int
     */
    public function stream_tell()
    {
        return ftell($this->fp);
    }

    /**
     * @param int $offset
     * @param int $whence
     *
     * @return int
     */
    public function stream_seek(int $offset, int $whence): int
    {
        return fseek($this->fp, $offset, $whence);
    }

    /**
     * @return bool
     */
    public function stream_flush(): bool
    {
        if (!is_resource($this->fp)) {
            return false;
        }

        return fflush($this->fp);
    }

    /**
     * @return array
     */
    public function stream_stat(): array
    {
        return fstat($this->fp);
    }
    // @codingStandardsIgnoreEnd
    // phpcs:enable

    /**
     * @param string $path
     *
     * @return bool
     */
    public function unlink(string $path): bool
    {
        return false;
    }

    /**
     * @param string $path_from
     * @param string $path_to
     *
     * @return bool
     */
    public function rename(string $path_from, string $path_to): bool
    {
        return false;
    }

    /**
     * @param string $path
     * @param int    $mode
     * @param mixed  $options
     *
     * @return bool
     */
    public function mkdir(string $path, $mode, $options): bool
    {
        return false;
    }

    /**
     * @param string $path
     * @param mixed  $options
     *
     * @return bool
     */
    public function rmdir($path, $options): bool
    {
        return false;
    }
}
