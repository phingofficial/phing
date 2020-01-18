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

/*
 * (c) Sebastian Bergmann <sebastian@phpunit.de>
 */
final class Console
{
    /**
     * @var int
     */
    public const STDIN  = 0;

    /**
     * @var int
     */
    public const STDOUT = 1;

    /**
     * @var int
     */
    public const STDERR = 2;

    /**
     * Returns true if STDOUT supports colorization.
     *
     * This code has been copied and adapted from
     * Symfony\Component\Console\Output\StreamOutput.
     */
    public static function hasColorSupport(): bool
    {
        if ('Hyper' === \getenv('TERM_PROGRAM')) {
            return true;
        }

        if (self::isWindows()) {
            // @codeCoverageIgnoreStart
            return (\defined('STDOUT') && \function_exists('sapi_windows_vt100_support') && @\sapi_windows_vt100_support(\STDOUT))
                || false !== \getenv('ANSICON')
                || 'ON' === \getenv('ConEmuANSI')
                || 'xterm' === \getenv('TERM');
            // @codeCoverageIgnoreEnd
        }

        if (!\defined('STDOUT')) {
            // @codeCoverageIgnoreStart
            return false;
            // @codeCoverageIgnoreEnd
        }

        return self::isInteractive(\STDOUT);
    }

    /**
     * Returns if the file descriptor is an interactive terminal or not.
     *
     * Normally, we want to use a resource as a parameter, yet sadly it's not always awailable,
     * eg when running code in interactive console (`php -a`), STDIN/STDOUT/STDERR constants are not defined.
     *
     * @param int|resource $fileDescriptor
     * @return bool
     */
    private static function isInteractive($fileDescriptor = self::STDOUT): bool
    {
        if (\is_resource($fileDescriptor)) {
            // These functions require a descriptor that is a real resource, not a numeric ID of it
            if (\function_exists('stream_isatty') && @\stream_isatty($fileDescriptor)) {
                return true;
            }

            $stat = @\fstat(\STDOUT);
            // Check if formatted mode is S_IFCHR
            return $stat ? 0020000 === ($stat['mode'] & 0170000) : false;
        }

        return \function_exists('posix_isatty') && @\posix_isatty($fileDescriptor);
    }

    private static function isWindows(): bool
    {
        return \DIRECTORY_SEPARATOR === '\\';
    }
}
